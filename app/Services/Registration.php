<?php
namespace CodeDay\Clear\Services;

use \Carbon\Carbon;
use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;
use \CodeDay\Clear\ModelContracts;

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
    
        try {
            (new \Customerio\Api(\config('customerio.site'), \config('customerio.secret'), new \Customerio\Request))
                ->createCustomer($reg->email, $reg->email, [
                    'type' => $reg->type,
                    'first_name' => $reg->first_name,
                    'last_name' => $reg->last_name,
                    'city' => $event->name,
                    'season' => $event->batch->name
                ]);
        } catch (\Exception $ex) {}

        \Event::fire('registration.register', [ModelContracts\Registration::Model($reg, ['admin', 'internal'])]);

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

    public static function GetCsv(Models\Batch\Event $event, $printHeader = true)
    {
        $registrations = iterator_to_array($event->registrations);

        // First, sort the registrations:
        usort($registrations, function($a, $b) {
            $typeSort = strcmp($a->type, $b->type);
            $lnameSort = strcmp($a->last_name, $b->last_name);
            $fnameSort = strcmp($b->first_name, $b->first_name);

            return $typeSort != 0 ? $typeSort : ($lnameSort != 0 ? $lnameSort : $fnameSort);
        });

        // Generate the header
        $header = [];
        if ($printHeader) {
            $header = [(object)[
                        'type' => 'type',
                        'last_name' => 'lastname',
                        'first_name' => 'firstname',
                        'email' => 'email',
                        'age' => 'age',
                        'promotion' => (object)['code' => 'promocode'],
                        'amount_paid' => 'paid',
                        'parent_name' => 'parentname',
                        'parent_email' => 'parentemail',
                        'parent_phone' => 'parentphone',
                        'parent_secondary_phone' => 'parentphonealt',
                        'checked_in_at' => 'checkedin',
                        'created_at' => 'created',
                        'event'     => (object)['webname' => 'event']]];
        }

        // Generate the file
        $content = implode("\n",
            array_map(function($reg) {
                return str_replace("\n", "", implode(',', [$reg->event->webname, $reg->type, $reg->last_name, $reg->first_name, $reg->email, $reg->age,
                    ($reg->promotion ? $reg->promotion->code : ''), $reg->amount_paid,
                    $reg->parent_name, $reg->parent_email, $reg->parent_phone, $reg->parent_secondary_phone,
                    $reg->checked_in_at, $reg->created_at]));
            }, array_merge($header, $registrations))
        );

        return $content;
    }

    public static function GetCsvMultiple($events)
    {
        $out = '';
        foreach ($events as $event) {
            $out .= self::GetCsv($event, strlen($out) === 0)."\n";
        }

        return $out;
    }
}
