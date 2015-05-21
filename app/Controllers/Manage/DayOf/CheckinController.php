<?php
namespace CodeDay\Clear\Controllers\Manage\DayOf;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class CheckinController extends \Controller {

    public function getIndex()
    {
        $this->checkAccess();
        return \View::make('dayof/checkin', ['event' => $this->getEvent()]);
    }

    public function postIndex()
    {
        $this->checkAccess();
        $event = $this->getEvent();
        $attendee_id = \Input::get('id');
        $action = \Input::get('action');

        $attendee = Models\Batch\Event\Registration::where('id', '=', $attendee_id)->firstOrFail();

        if (!$event || $event->id !== $attendee->batches_event_id) {
            \App::abort(404);
        }

        if ($action == 'in') {
            $attendee->checked_in_at = \Carbon\Carbon::now();
            $attendee->save();
            \Event::fire('registration.checkin', \DB::table('batches_events_registrations')->where('id', '=', $attendee_id)->get()[0]);
        } else {
            $attendee->checked_in_at = null;
            $attendee->save();
            \Event::fire('registration.checkout', \DB::table('batches_events_registrations')->where('id', '=', $attendee_id)->get()[0]);
        }

        return json_encode((object)['status' => 200]);
    }

    private function getEvent()
    {
        $event_id = \Input::get('event');
        return Models\Batch\Event::where('id', '=', $event_id)->first();
    }

    private function checkAccess()
    {
        $event = $this->getEvent();

        if (!$event) {
            return true;
        }

        if (Models\User::me()->username != $event->manager_username
            && Models\User::me()->username != $event->evangelist_username
            && !$event->isUserAllowed(Models\User::me())
            && !Models\User::me()->is_admin) {
            \App::abort(401);
        }
    }
}
