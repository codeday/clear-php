<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

class ShippingController extends \CodeDay\Clear\Http\Controller {
    public function getIndex()
    {
        $event = \Route::input('event');
        if ($event->shipment_number != null) {
            \App::abort(401);
        }

        return \View::make('event/shipping');
    }

    public function postIndex()
    {
        $event = \Route::input('event');
        if ($event->shipment_number != null) {
            \App::abort(401);
        }

        if (\Input::get('copy')) {
            $event->ship_name = $event->venue_name;
            $event->ship_company = "ATTN CODEDAY";
            $event->ship_address_1 = $event->venue_address_1;
            $event->ship_address_2 = $event->venue_address_2;
            $event->ship_city = $event->venue_city;
            $event->ship_state = $event->venue_state;
            $event->ship_postal = $event->venue_postal;
            $event->ship_country = $event->venue_country;
            $event->ship_is_residential = false;
        } else {
            $event->ship_name = \Input::get('ship_name');
            $event->ship_company = \Input::get('ship_company');
            $event->ship_address_1 = \Input::get('ship_address_1');
            $event->ship_address_2 = \Input::get('ship_address_2');
            $event->ship_city = \Input::get('ship_city');
            $event->ship_state = \Input::get('ship_state');
            $event->ship_postal = \Input::get('ship_postal');
            $event->ship_country = \Input::get('ship_country');
            $event->ship_is_residential = \Input::get('ship_is_residential') ? true : false;
        }

        \Session::flash('status_message', 'Shipping information updated');

        $event->save();
        return \Redirect::to('/event/'.$event->id.'/shipping');
    }
} 