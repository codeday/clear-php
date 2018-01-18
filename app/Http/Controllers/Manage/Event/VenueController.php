<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class VenueController extends \CodeDay\Clear\Http\Controller {
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

        $event->venue_name = \Input::get('venue_name') ? \Input::get('venue_name') : null;
        $event->venue_address_1 = \Input::get('venue_address_1') ? \Input::get('venue_address_1') : null;
        $event->venue_address_2 = \Input::get('venue_address_2') ? \Input::get('venue_address_2') : null;
        $event->venue_city = \Input::get('venue_city') ? \Input::get('venue_city') : null;
        $event->venue_state = \Input::get('venue_state') ? \Input::get('venue_state') : null;
        $event->venue_postal = \Input::get('venue_postal') ? \Input::get('venue_postal') : null;
        $event->venue_country= \Input::get('venue_country') ? \Input::get('venue_country') : null;
        $event->max_registrations = \Input::get('max_registrations') ? \Input::get('max_registrations') : null;
        $event->loaners_available = \Input::get('loaners_available') ? \Input::get('loaners_available') : 0;

        $event->venue_contact_first_name = \Input::get('venue_contact_first_name') ? \Input::get('venue_contact_first_name') : null;
        $event->venue_contact_last_name = \Input::get('venue_contact_last_name') ? \Input::get('venue_contact_last_name') : null;
        $event->venue_contact_email = \Input::get('venue_contact_email') ? \Input::get('venue_contact_email') : null;
        $event->venue_contact_phone = \Input::get('venue_contact_phone') ? \Input::get('venue_contact_phone') : null;

        \Session::flash('status_message', 'Venue updated');

        $event->save();
        return \Redirect::to('/event/'.$event->id.'/venue');
    }
} 
