<?php
namespace CodeDay\Clear\Services;

use \Carbon\Carbon;
use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

/**
 * Registration CRUD interface for front-end registration flows.
 *
 * The Registration service has methods for creating, updating, and deleting registrations, as well as dispatching
 * related events (such as enqueuing a pre-event survey email). It's intended to be an interface for most front-end
 * registration tasks.
 *
 * @package     CodeDay\Clear\Services
 * @author      Tyler Menezes <tylermenezes@studentrnd.org>
 * @copyright   (c) 2014-2015 StudentRND
 * @license     Perl Artistic License 2.0
 */
class Registration {
    public static function CreateRegistrationRecord(Models\Batch\Event $event, $firstName, $lastName, $email, $type)
    {
        $reg = new Models\Batch\Event\Registration;
        $reg->batches_event_id = $event->id;
        $reg->first_name = $firstName;
        $reg->last_name = $lastName;
        $reg->email = $email;
        $reg->type = $type;
        $reg->save();

        \Event::fire('registration.register', [$reg]);

        return $reg;
    }

    public static function ChargeCardForRegistrations($registrations, $totalCost, $cardToken)
    {
        $event = $registrations[0]->event;

        // Build the description for Stripe
        $forDescriptor = implode(', ', array_map(function($reg) {
            return $reg->name;
        }, $registrations));
        $forDescriptor = 'CodeDay '.$event->name. ' Registration:'.$forDescriptor;

        // Make the charge
        \Stripe\Stripe::setApiKey(\Config::get('stripe.secret'));
        $charge = \Stripe\Charge::create([
            'currency' => 'usd',
            'statement_description' => 'CODEDAY',

            'amount' => intval($totalCost * 100),
            'card' => $cardToken,
            'description' => $forDescriptor,
            'metadata' => [
                'registrations_count' => count($registrations),
                'region' => $event->webname
            ]
        ]);

        $stripeFee = ($totalCost * 0.027) + 0.30;
        $amountReceived = $totalCost - $stripeFee;

        // Update the registrants' billing status
        foreach ($registrations as $reg) {
            $reg->stripe_id = $charge->id;
            $reg->amount_paid = $totalCost / count($registrations);
            $reg->is_earlybird_pricing = $reg->event->is_earlybird_pricing;
            $reg->save();
        }
    }

    public static function CancelRegistration(Models\Batch\Event\Registration $registration,
                                              $andRefund = true, $cancelRelated = false)
    {
        if ($andRefund && $registration->stripe_id) {
            \Stripe\Stripe::setApiKey(\Config::get('stripe.secret'));
            $charge = \Stripe\Charge::retrieve($registration->stripe_id);

            if ($cancelRelated || count($registration->all_in_order) == 1) {
                $charge->refunds->create();
            } else {
                $charge->refunds->create([
                    'amount' => $registration->amount_paid * 100
                ]);
            }
        }

        $registration->amount_refunded += $registration->amount_paid;
        $registration->amount_paid = 0;
        $registration->save();

        $all_in_order = $registration->all_in_order;
        $registration->delete();

        if ($cancelRelated) {
            foreach ($all_in_order as $reg) {
                self::CancelRegistration($reg, false, false);
            }
        }
    }

    public static function PartiallyRefundRegistration(Models\Batch\Event\Registration $registration, $refundAmount)
    {
        \Stripe\Stripe::setApiKey(\Config::get('stripe.secret'));
        $charge = \Stripe\Charge::retrieve($registration->stripe_id);

        if ($registration->amount_paid == $refundAmount) {
            $charge->refunds->create();
        } elseif ($registration->amount_paid > $refundAmount) {
            $charge->refunds->create([
                'amount' => $refundAmount * 100
            ]);
        } else {
            throw new \Exception("Cannot refund more than the original ticket price.");
        }

        $registration->amount_paid -= $refundAmount;
        $registration->amount_refunded += $refundAmount;
        $registration->save();
    }

    public static function SendTicketEmail(Models\Batch\Event\Registration $reg)
    {
        // NOOP until we can fully remove this
    }

    public static function EnqueueSurveyEmail(Models\Batch\Event\Registration $reg)
    {
        // NOOP until we can fully remove this
    }

    public static function SendPartialRefundEmail(Models\Batch\Event\Registration $reg, $refundAmount)
    {
        Services\Email::SendOnQueue(
            'CodeDay '.$reg->event->name, $reg->event->webname.'@codeday.org',
            $reg->name, $reg->email,
            'Ticket Refunded: CodeDay '.$reg->event->name,
            \View::make('emails/registration/refund', [
                'registration' => $reg,
                'amount' => $refundAmount
            ])
        );
    }

    public static function SendCancelEmail(Models\Batch\Event\Registration $reg, $refund)
    {
        Services\Email::SendOnQueue(
            'CodeDay '.$reg->event->name, $reg->event->webname.'@codeday.org',
            $reg->name, $reg->email,
            'Ticket Cancelled: CodeDay '.$reg->event->name,
            \View::make('emails/registration/cancel', [
                'registration' => $reg,
                'refund' => $refund
            ])
        );
    }
}
