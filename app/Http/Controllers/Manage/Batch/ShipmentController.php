<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class ShipmentController extends \CodeDay\Clear\Http\Controller {
    public function getAmounts()
    {
        return \View::make('batch/shipments/amounts');
    }

    public function postAmounts()
    {
        $ship_fors = \Input::get('ship_fors');
        foreach ($ship_fors as $event_id => $ship_for) {
            $event = Models\Batch\Event::where('id', '=', $event_id)->firstOrFail();
            if ($event->shipment_number != null) {
                continue;
            }
            $event->ship_for = $ship_for ? $ship_for : null;
            $event->save();
        }
        \Session::flash('status_message', 'Shipments saved');
        return \Redirect::to('/batch/shipments/amounts');
    }

    public function getPush()
    {
        return \View::make('batch/shipments/push');
    }

    public function postPush()
    {
        $count = 0;
        foreach (Models\Batch::Managed()->events as $event) {
            if (!$event->ship_address_1 || !$event->ship_city || !$event->ship_state || !$event->ship_postal)
                continue;

            $order = Services\Ship::ToEvent('EVT-'.$event->id, $event, $event->manifestGenerated);
            Services\Ship::Tag($order, \Config::get('shipstation.tags.event_supplies'));


            $event->shipstation_id = $order;
            $event->save();
            
            $count++;
        }

        \Session::flash('status_message', 'Orders created/updated for '.$count.' events.');
        return \Redirect::to('/batch/shipments/push');
    }
}
