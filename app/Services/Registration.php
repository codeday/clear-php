<?php
namespace CodeDay\Clear\Services;

use \Carbon\Carbon;
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
        $reg->s5_invite_code = substr(str_pad(base_convert(md5(mt_rand().microtime(true)), 16, 36), 25, '0'), 0, 8);
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
        \Stripe::setApiKey(\Config::get('stripe.secret'));
        $charge = \Stripe_Charge::create([
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
        Services\Email::SendOnQueue(
            'CodeDay '.$reg->event->name, $reg->event->webname.'@codeday.org',
            $reg->name, $reg->email,
            'Your CodeDay '.$reg->event->name.' Tickets',
            \View::make('emails/registration_text', ['registration' => $reg]),
            \View::make('emails/registration_html', ['registration' => $reg])
        );

        // Schedule the pre-event email to be sent to this attendee if it's already been sent to everyone else
        if ($reg->event->batch->preevent_email_sent_at !== null) {
            $sendAt = Carbon::now()->addMinutes(rand(10, 30))->addSeconds(rand(0,60));
            $delay = $sendAt->timestamp - Carbon::now()->timestamp;
            $timeToEvent = $reg->event->starts_at - Carbon::now()->timestamp;
            if ($timeToEvent < $delay) {
                $delay = 1;
            }
            Services\Email::LaterOnQueue(
                $delay,
                'CodeDay '.$reg->event->name, $reg->event->webname.'@codeday.org',
                $reg->name, $reg->email,
                'CodeDay is Shortly Upon Us',
                \View::make('emails/preevent_text', ['registration' => $reg]),
                \View::make('emails/preevent_html', ['registration' => $reg])
            );
        }
    }

    public static function EnqueueSurveyEmail(Models\Batch\Event\Registration $reg)
    {
        $officeHoursStart = 9;
        $officeHoursEnd = 17;
        $officeDays = [Carbon::MONDAY, Carbon::TUESDAY, Carbon::WEDNESDAY, Carbon::THURSDAY, Carbon::FRIDAY];

        $sendAt = Carbon::now()->addMinutes(rand(10, 120))->addSeconds(rand(0,60));

        if ($sendAt->hour < $officeHoursStart) {
            $sendAt->hour = $officeHoursStart;
        } else if ($sendAt->hour > $officeHoursEnd) {
            $sendAt->addDay()->hour = $officeHoursStart;
        }

        while (!in_array($sendAt->dayOfWeek, $officeDays)) {
            $sendAt->addDay();
        }

        $delay = $sendAt->timestamp - Carbon::now()->timestamp;

        Services\Email::LaterOnQueue(
            $delay,
            'Tyler Menezes', 'menezest@codeday.org',
            $reg->name, $reg->email,
            'CodeDay',
            \View::make('emails/postreg_survey', ['registration' => $reg]),
            null,
            true
        );
    }

    public static function SendPartialRefundEmail(Models\Batch\Event\Registration $reg, $refundAmount)
    {
        Services\Email::SendOnQueue(
            'CodeDay '.$reg->event->name, $reg->event->webname.'@codeday.org',
            $reg->name, $reg->email,
            'Ticket Refunded: CodeDay '.$reg->event->name,
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
