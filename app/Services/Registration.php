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
        $forDescriptor = 'CodeDay '.$event->region->name. 'Registration:'.$forDescriptor;

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
            $reg->amount_received = $amountReceived / count($registrations);
            $reg->is_earlybird_pricing = $reg->event->is_earlybird_pricing;
            $reg->save();
        }
    }

    public static function SendTicketEmail(Models\Batch\Event\Registration $reg)
    {
        Services\Email::SendOnQueue(
            'CodeDay '.$reg->event->name, $reg->event->webname.'@codeday.org',
            $reg->name, $reg->email,
            'Your Tickets for CodeDay '.$reg->event->name,
            \View::make('emails/registration_text', ['registration' => $reg]),
            \View::make('emails/registration', ['registration' => $reg])
        );
    }
} 