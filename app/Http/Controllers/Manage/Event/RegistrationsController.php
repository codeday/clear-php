<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;
use \CodeDay\Clear\Services;

class RegistrationsController extends \CodeDay\Clear\Http\Controller {
    public function getIndex()
    {
        $event = \Route::input('event');
        if(\Input::get('sort')){
          $sort = \Input::get('sort');
          $order = \Input::get('order') == 'desc' ? 'desc' : 'asc';
          $registrations = $event->registrationsSortedBy($sort, $order);
        }else{
          $registrations = $event->registrationsSortedBy("created_at");
        }
        return \View::make('event/registrations/index', ['signature' => $this->getListSignature(), 'registrations' => $registrations]);
    }

    public function getCsv()
    {
        $event = \Route::input('event');
        if (\Input::get('signature') != $this->getListSignature()) {
            \App::abort(403);
        }

        return \View::make('csv-landing', ['signature' => $this->getListSignature()]);
    }

    public function getDownloadcsv()
    {
        $event = \Route::input('event');
        if (\Input::get('signature') != $this->getListSignature()) {
            \App::abort(403);
        }

        $content = Services\Registration::GetCsv($event);

        return (new \Illuminate\Http\Response($content, 200))
            ->header('Content-type', 'text/csv')
            ->header('Content-disposition', 'attachment;filename='.$event->webname.'-attendees-'.time().'.csv');
    }

    private function getListSignature()
    {
        $event = \Route::input('event');
        return hash_hmac('sha256', \Config::get('app.key'), $event->id);
    }

    public function postAdd()
    {
        $event = \Route::input('event');

        $registration = Services\Registration::CreateRegistrationRecord(
            $event,
            \Input::get('first_name'), \Input::get('last_name'),
            \Input::get('email'), \Input::get('type'));

        if ($registration->type !== 'student') {
            $registration->parent_no_info = true;
        }
        $registration->save();

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

        $registration->email = \Input::get('email');
        $registration->first_name = \Input::get('first_name');
        $registration->last_name = \Input::get('last_name');
        $registration->type = \Input::get('type');

        if (\Input::get('age')) {
            $registration->age = \Input::get('age');
        }
        if ($registration->age) {
            $registration->parent_no_info = $registration->age >= 18;
            if ($registration->parent_no_info) {
                $registration->parent_name = null;
                $registration->parent_email = null;
                $registration->parent_phone = null;
                $registration->parent_secondary_phone = null;
            } else {
                $registration->parent_name = \Input::get('parent_name');
                $registration->parent_email = \Input::get('parent_email');
                $registration->parent_phone = \Input::get('parent_phone');
                $registration->parent_secondary_phone = \Input::get('parent_secondary_phone');
            }
        }
        $registration->save();

        \Session::flash('status_message', $registration->name.' updated');
        return \Redirect::to('/event/'.$event->id.'/registrations/attendee/'.$registration->id);
    }

    public function postNotes()
    {
        $event = \Route::input('event');
        $registration = \Route::input('registration');
        if ($registration->batches_event_id != $event->id) {
            \App::abort(404);
        }

        $registration->notes = \Input::get('notes') ? \Input::get('notes') : null;
        $registration->save();

        \Session::flash('status_message', $registration->name."'s notes were updated.");

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

    public function postRemovedevices()
    {
        $event = \Route::input('event');
        $registration = \Route::input('registration');

        if(Models\User::me()->is_admin) {
            foreach($device in $registration->devices) {
                $device->delete();
            }

            \Session::flash('status_message', 'Devices removed');
            return \Redirect::to('/event/'.$event->id.'/registrations/attendee/'.$registration->id);
        } else {
            \App::abort(401);
        }
    }

    public function getWaiver()
    {
        $event = \Route::input('event');
        $registration = \Route::input('registration');
        return "One-time use only! ".$registration->waiver->signers[0]->getLink();
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

    public function postResend()
    {
        $event = \Route::input('event');
        $registration = \Route::input('registration');
        $email = Models\TransactionalEmail::where('id', '=', \Input::get('id'))->firstOrFail();
        if ($registration->batches_event_id != $event->id ||
            $email->batches_events_registration_id !== $registration->id) {
            \App::abort(404);
        }

        $email->delete();

        \Session::flash('status_message', 'Email queued for re-sending.');
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
                \View::make('emails/registration/transfer', [
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

        \Session::flash('status_message', $registration->name.' was transferred to '.$toEvent->name);

        if (Models\User::me()->username != $toEvent->manager_username
            && !$toEvent->isUserAllowed(Models\User::me())
            && !Models\User::me()->is_admin) {
            return \Redirect::to('/event/'.$event->id.'/registrations');
        } else {
            return \Redirect::to('/event/'.$toEvent->id.'/registrations/attendee/'.$registration->id);
        }
    }

    public function postWebhook(){
      if(!Models\User::me()->is_admin){
        \Session::flash('error', "You must be an admin to fire a webhook manually");
        return \Redirect::to('/event/'.$event->id.'/registrations/attendee/'.$registration->id);
      }

      $event = \Route::input('event');
      $registration = \Route::input('registration');
      $hook = \Input::get('hook_event');

      \Event::fire($hook, [ModelContracts\Registration::Model($registration, ['admin', 'internal'])]);
      \Session::flash('status_message', $hook.' hook queued for '.$registration->name);
      return \Redirect::to('/event/'.$event->id.'/registrations/attendee/'.$registration->id);
    }
}
