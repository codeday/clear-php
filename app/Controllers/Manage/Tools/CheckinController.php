<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class CheckinController extends \Controller {

    public function getIndex()
    {
        $this->checkAccess();
        return \View::make('tools/checkin', ['event' => $this->getEvent()]);
    }

    public function postIndex()
    {
        $this->checkAccess();
        $attendee_id = \Input::get('id');
        $action = \Input::get('action');

        $attendee = Models\Batch\Event\Registration::where('id', '=', $attendee_id)->firstOrFail();

        if ($action == 'in') {
            $attendee->checked_in_at = \Carbon\Carbon::now();
            $attendee->save();
        } else {
            $attendee->checked_in_at = null;
            $attendee->save();
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
        if (Models\User::me()->username != $event->manager_username
            && Models\User::me()->username != $event->evangelist_username
            && !$event->isUserAllowed(Models\User::me())
            && !Models\User::me()->is_admin) {
            \App::abort(401);
        }
    }
}