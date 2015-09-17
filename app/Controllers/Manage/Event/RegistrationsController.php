<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class RegistrationsController extends \Controller {
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

        $content = implode("\n",
            array_map(function($reg) {
                return implode(',', [$reg->last_name, $reg->first_name, $reg->email,
                    ($reg->promotion ? $reg->promotion->code : ''), $reg->amount_paid,
                    $reg->parent_name, $reg->parent_email, $reg->parent_phone, $reg->parent_secondary_phone,
                    $reg->checked_in_at, $reg->created_at]);
            }, array_merge(
                [(object)[
                    'last_name' => 'lastname',
                    'first_name' => 'firstname',
                    'email' => 'email',
                    'promotion' => (object)['code' => 'promocode'],
                    'amount_paid' => 'paid',
                    'parent_name' => 'parentname',
                    'parent_email' => 'parentemail',
                    'parent_phone' => 'parentphone',
                    'parent_secondary_phone' => 'parentphonealt',
                    'checked_in_at' => 'checkedin',
                    'created_at' => 'created']],
                iterator_to_array($event->registrations)
            ))
        );
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
            \Input::get('email'));

        $registration->type = \Input::get('type');
        if ($registration->type !== 'student') {
            $registration->parent_no_info = true;
        }
        $registration->save();

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
        $registration->type = \Input::get('type');
        $registration->parent_name = \Input::get('parent_name');
        $registration->parent_email = \Input::get('parent_email');
        $registration->parent_phone = \Input::get('parent_phone');
        $registration->parent_secondary_phone = \Input::get('parent_secondary_phone');
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

      \Event::fire($hook, [$registration]);
      \Session::flash('status_message', $hook.' hook queued for '.$registration->name);
      return \Redirect::to('/event/'.$event->id.'/registrations/attendee/'.$registration->id);
    }
}
