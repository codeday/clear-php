<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

class ShippingController extends \Controller {
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

        $event->ship_name = \Input::get('ship_name');
        $event->ship_company = \Input::get('ship_company');
        $event->ship_address_1 = \Input::get('ship_address_1');
        $event->ship_address_2 = \Input::get('ship_address_2');
        $event->ship_city = \Input::get('ship_city');
        $event->ship_state = \Input::get('ship_state');
        $event->ship_postal = \Input::get('ship_postal');
        $event->ship_country = \Input::get('ship_country');
        $event->ship_is_resedential = \Input::get('ship_is_resedential') ? true : false;

        $event->save();
        return \Redirect::to('/event/'.$event->id.'/shipping');
    }
} 