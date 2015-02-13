<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class VenueController extends \Controller {
    public function getIndex()
    {
        return \View::make('event/venue');
    }

    public function postIndex()
    {
        $event = \Route::input('event');

        if (\Input::get('max_registrations') > 120 && $event->max_registrations <= 120 && !Models\User::me()->is_admin) {
            \Session::flash('error', 'Capacity cannot be greater than 120. (Contact an admin to override this.)');
            return \Redirect::to('/event/'.$event->id.'/venue');
        }

        $event->venue_name = \Input::get('venue_name');
        $event->venue_address_1 = \Input::get('venue_address_1');
        $event->venue_address_2 = \Input::get('venue_address_2');
        $event->venue_city = \Input::get('venue_city');
        $event->venue_state = \Input::get('venue_state');
        $event->venue_postal = \Input::get('venue_postal');
        $event->venue_country= \Input::get('venue_country');
        $event->waiver_link = \Input::get('waiver_link');
        $event->max_registrations = \Input::get('max_registrations');

        \Session::flash('status_message', 'Venue updated');

        $event->save();
        return \Redirect::to('/event/'.$event->id.'/venue');
    }
} 