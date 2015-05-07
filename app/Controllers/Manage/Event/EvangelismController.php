<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class EvangelismController extends \Controller {
    public function getIndex()
    {
        return \View::make('event/evangelism');
    }

    public function postAddflight()
    {
        $event = \Route::input('event');

        if (!Models\User::me()->is_admin) {
            \App::abort(403);
        }

        $flight = new Models\Batch\Event\Flight;
        $flight->batches_event_id = $event->id;
        $flight->confirmation_code = \Input::get('confirmation_code');
        $flight->airline = \Input::get('airline');
        $flight->flight_number = \Input::get('flight_number');
        $flight->from_airport = \Input::get('from_airport');
        $flight->departs_at = \Input::get('departs_at');
        $flight->to_airport = \Input::get('to_airport');
        $flight->arrives_at = \Input::get('arrives_at');
        $flight->traveler_username = \Input::get('traveler_username');
        $flight->direction = \Input::get('direction');
        $flight->save();

        return \Redirect::to('/event/'.$event->id.'/evangelism');
    }
}
