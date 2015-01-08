<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class RegistrationsController extends \Controller {
    public function getIndex()
    {
        return \View::make('event/registrations/index');
    }

    public function postAdd()
    {
        $event = \Route::input('event');

        $registration = Services\Registration::CreateRegistrationRecord(
            $event,
            \Input::get('first_name'), \Input::get('last_name'),
            \Input::get('email'));

        if (\Input::get('send_welcome')) {
            Services\Registration::SendTicketEmail($registration);
            Services\Registration::EnqueueSurveyEmail($registration);
        }

        \Session::flash('status_message', $registration->name.' was registered');

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
            Services\Registration::SendTicketEmail($registration);
        }

        \Session::flash('status_message', $registration->name.' updated');

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

        if (\Input::get('email')) {
            foreach ($to_cancel as $reg) {
                Services\Registration::SendCancelEmail($reg, boolval(\Input::get('refund')));
            }
        }

        \Session::flash('status_message', $registration->name.'\'s registration was cancelled');

        Services\Registration::CancelRegistration($registration,
            boolval(\Input::get('refund')), boolval(\Input::get('related')));

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
            \Session::flash('error', 'Not a valid refund amount');
            return \Redirect::to('/event/'.$event->id.'/registrations/attendee/'.$registration->id);
        }

        Services\Registration::PartiallyRefundRegistration($registration, $amount);

        if (\Input::get('email')) {
            Services\Registration::SendPartialRefundEmail($registration, $amount);
        }

        \Session::flash('status_message', $registration->name.' was refunded $'.number_format($amount, 2));

        return \Redirect::to('/event/'.$event->id.'/registrations/attendee/'.$registration->id);
    }

    public function postTransfer()
    {
        $event = \Route::input('event');
        $registration = \Route::input('registration');
        if ($registration->batches_event_id != $event->id) {
            \App::abort(404);
        }

        $toEvent = Models\Batch\Event
            ::where('id', '=', \Input::get('id'))
            ->where('batch_id', '=', $event->batch->id)
            ->firstOrFail();

        if (\Input::get('email')) {
            Services\Email::SendOnQueue(
                'CodeDay', 'support@codeday.org',
                $registration->name, $registration->email,
                'Ticket Transferred: CodeDay '.$event->name.' to '.$toEvent->name,
                \View::make('emails/reg_transfer', [
                    'registration' => $registration,
                    'from_event' => $event,
                    'to_event' => $toEvent
                ])
            );
        }

        if (($toEvent->remaining_registrations < 0)
            && !(Models\User::me()->username != $toEvent->manager_username
                && !$toEvent->isUserAllowed(Models\User::me())
                && !Models\User::me()->is_admin)) {

            \Session::flash('error', 'You cannot transfer to a sold-out event which you do not manage');
            return \Redirect::to('/event/'.$event->id.'/registrations/attendee/'.$registration->id);
        }

        $registration->batches_event_id = $toEvent->id;
        $registration->save();

        \Session::flash('status_message', $registration->name.' was transferred to '.$toEvent->region->name);

        if (Models\User::me()->username != $toEvent->manager_username
            && !$toEvent->isUserAllowed(Models\User::me())
            && !Models\User::me()->is_admin) {
            return \Redirect::to('/event/'.$event->id.'/registrations');
        } else {
            return \Redirect::to('/event/'.$toEvent->id.'/registrations/attendee/'.$registration->id);
        }
    }
} 
