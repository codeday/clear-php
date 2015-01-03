<?php
namespace CodeDay\Clear\Services;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class Registration {
    public static function CreateRegistrationRecord(Models\Batch\Event $event, $firstName, $lastName, $email)
    {
        $reg = new Models\Batch\Event\Registration;
        $reg->batches_event_id = $event->id;
        $reg->first_name = $firstName;
        $reg->last_name = $lastName;
        $reg->email = $email;
        $reg->save();

        return $reg;
    }

    public static function ChargeCardForRegistrations($registrations, $totalCost, $cardToken)
    {
        $event = $registrations[0]->event;

        // Build the description for Stripe
        $forDescriptor = implode(', ', array_map(function($reg) {
            return $reg->name;
        }, $registrations));
        $forDescriptor = 'CodeDay '.$event->region->name. ' Registration:'.$forDescriptor;

        // Make the charge
        \Stripe::setApiKey(\Config::get('stripe.secret'));
        $charge = \Stripe_Charge::create([
            'currency' => 'usd',
            'statement_description' => 'CODEDAY',

            'amount' => intval($totalCost * 100),
            'card' => $cardToken,
            'description' => $forDescriptor,
            'metadata' => [
                'registrations_count' => count($registrations),
                'region' => $event->region->webname
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
            \Stripe::setApiKey(\Config::get('stripe.secret'));
            $charge = \Stripe_Charge::retrieve($registration->stripe_id);

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
        \Stripe::setApiKey(\Config::get('stripe.secret'));
        $charge = \Stripe_Charge::retrieve($registration->stripe_id);

        if ($registration->amount_paid == $refundAmount) {
            $charge->refunds->create();
        } elseif ($registration->amount_paid > $refundAmount) {
            $charge->refunds->create([
                'amount' => $registration->amount_paid * 100
            ]);
        } else {
            throw new \Exception("Cannot refund more than the original ticket price.");
        }

        $registration->amount_paid -= $refundAmount;
        $registration->amount_refunded += $registration->refundAmount;
        $registration->save();
    }

    public static function SendTicketEmail(Models\Batch\Event\Registration $reg)
    {
        Services\Email::SendOnQueue(
            'CodeDay '.$reg->event->name, $reg->event->webname.'@codeday.org',
            $reg->name, $reg->email,
            'Your CodeDay '.$reg->event->name.' Tickets',
            \View::make('emails/registration_text', ['registration' => $reg]),
            \View::make('emails/registration', ['registration' => $reg])
        );
    }

    public static function SendPartialRefundEmail(Models\Batch\Event\Registration $reg, $refundAmount)
    {
        Services\Email::SendOnQueue(
            'CodeDay '.$reg->event->name, $reg->event->webname.'@codeday.org',
            $reg->name, $reg->email,
            'Ticket Cancelled: CodeDay '.$reg->event->name,
            \View::make('emails/reg_refund', [
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
            \View::make('emails/reg_cancel', [
                'registration' => $reg,
                'refund' => $refund
            ])
        );
    }
} 