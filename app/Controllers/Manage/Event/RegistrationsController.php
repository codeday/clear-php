<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class RegistrationsController extends \Controller {
    public function getIndex()
    {
        return \View::make('event/registrations/index');
    }

    public function postAdd()
    {
        $event = \Route::input('event');

        $registration = new Models\Batch\Event\Registration;
        $registration->first_name = \Input::get('first_name');
        $registration->last_name = \Input::get('last_name');
        $registration->email = \Input::get('email');
        $registration->batches_event_id = $event->id;
        $registration->save();

        if (\Input::get('send_welcome')) {
            \Mail::send(['emails/registration', 'emails/registration_text'], [
                    'first_name' => $registration->first_name,
                    'last_name' => $registration->last_name,
                    'total_cost' => 0,
                    'unit_cost' => 0,
                    'event' => $event
                ], function($envelope) use ($registration, $event) {
                    $envelope->from($event->webname.'@codeday.org', 'CodeDay '.$event->name);
                    $envelope->to($registration->email, $registration->name);
                    $envelope->subject('Your Tickets for CodeDay '.$event->name);
                });
        }

        return \Redirect::to('/event/'.$event->id.'/registrations');
    }

    public function getAttendee()
    {
        $event = \Route::input('event');
        $registration = \Route::input('registration');
        if ($registration->batches_event_id != $event->id) {
            \App::abort(404);
        }

        return \View::make('event/registrations/attendee', ['registration' => $registration]);
    }

    public function postAttendee()
    {
        $event = \Route::input('event');
        $registration = \Route::input('registration');
        if ($registration->batches_event_id != $event->id) {
            \App::abort(404);
        }

        $registration->first_name = \Input::get('first_name');
        $registration->last_name = \Input::get('last_name');
        $registration->email = \Input::get('email');
        $registration->save();

        if (\Input::get('resend')) {
            \Mail::send(['emails/registration', 'emails/registration_text'], [
                'first_name' => $registration->first_name,
                'last_name' => $registration->last_name,
                'total_cost' => $registration->amount_paid,
                'unit_cost' => $registration->amount_paid,
                'event' => $event
            ], function($envelope) use ($registration, $event) {
                $envelope->from($event->webname.'@codeday.org', 'CodeDay '.$event->name);
                $envelope->to($registration->email, $registration->first_name.' '.$registration->last_name);
                $envelope->subject('Your Tickets for CodeDay '.$event->name);
            });
        }

        return \Redirect::to('/event/'.$event->id.'/registrations/attendee/'.$registration->id);
    }

    public function postCancel()
    {
        $event = \Route::input('event');
        $registration = \Route::input('registration');
        if ($registration->batches_event_id != $event->id) {
            \App::abort(404);
        }

        $to_cancel = \Input::get('related') ? $registration->all_in_order : [$registration];

        // Process refund if applicable
        if (\Input::get('refund') && $registration->stripe_id) {
            \Stripe::setApiKey(\Config::get('stripe.secret'));
            $charge = \Stripe_Charge::retrieve($registration->stripe_id);

            if (\Input::get('related') || count($registration->all_in_order) == 1) {
                $charge->refunds->create();
            } else {
                $refund_amount = 0.0;
                foreach ($to_cancel as $reg) {
                    $refund_amount += $reg->amount_paid;
                }

                $charge->refunds->create([
                    'amount' => $refund_amount * 100
                ]);
            }
        }

        if (\Input::get('email')) {
            foreach ($to_cancel as $reg) {
                \Mail::queue('emails/reg_cancel', [
                    'first_name' => $reg->first_name,
                    'last_name' => $reg->last_name,
                    'refund' => \Input::get('refund') ? true : false
                ], function($envelope) use ($reg, $event) {
                    $envelope->from('contact@studentrnd.org', 'StudentRND');
                    $envelope->to($reg->email, $reg->name);
                    $envelope->subject('Ticket Cancelled: CodeDay '.$event->name);
                });
            }
        }

        // Delete the tickets
        foreach ($to_cancel as $reg) {
            $reg->delete();
        }

        return \Redirect::to('/event/'.$event->id.'/registrations');
    }


    public function postRefund()
    {
        $event = \Route::input('event');
        $registration = \Route::input('registration');
        if ($registration->batches_event_id != $event->id) {
            \App::abort(404);
        }

        $amount = floatval(\Input::get('amount'));

        if ($amount > $registration->amount_paid || $amount <= 0) {
            return "Amount is invalid.";
        }

        \Stripe::setApiKey(\Config::get('stripe.secret'));
        $charge = \Stripe_Charge::retrieve($registration->stripe_id);
        $charge->refunds->create([
            'amount' => $amount * 100
        ]);

        $registration->amount_paid -= $amount;
        $registration->save();

        if (\Input::get('email')) {
            \Mail::queue('emails/reg_refund', [
                'first_name' => $registration->first_name,
                'last_name' => $registration->last_name,
                'amount' => $amount
            ], function($envelope) use ($registration, $event) {
                $envelope->from('contact@studentrnd.org', 'StudentRND');
                $envelope->to($registration->email, $registration->name);
                $envelope->subject('Refund: CodeDay '.$event->name);
            });
        }

        return \Redirect::to('/event/'.$event->id.'/registrations/attendee/'.$registration->id);
    }
} 