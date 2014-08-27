<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

class VenueController extends \Controller {
    public function getIndex()
    {
        return \View::make('event/venue');
    }

    public function postIndex()
    {
        $event = \Route::input('event');

        $event->venue_name = \Input::get('venue_name');
        $event->venue_address_1 = \Input::get('venue_address_1');
        $event->venue_address_2 = \Input::get('venue_address_2');
        $event->venue_city = \Input::get('venue_city');
        $event->venue_state = \Input::get('venue_state');
        $event->venue_postal = \Input::get('venue_postal');
        $event->venue_country= \Input::get('venue_country');
        $event->waiver_link = \Input::get('waiver_link');

        $event->save();
        return \Redirect::to('/event/'.$event->id.'/venue');
    }
} 