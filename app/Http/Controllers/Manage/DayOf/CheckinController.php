<?php
namespace CodeDay\Clear\Http\Controllers\Manage\DayOf;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class CheckinController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('dayof/checkin');
    }

    public function postIndex()
    {
        $event = getDayOfEvent();
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
}
