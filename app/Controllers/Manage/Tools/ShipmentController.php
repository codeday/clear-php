<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class ShipmentController extends \Controller {

    public function getIndex()
    {
        return \View::make('tools/shipments');
    }

    public function postUpdate()
    {
        $lengths = \Input::get('lengths');
        $widths = \Input::get('widths');
        $heights = \Input::get('heights');
        $weights = \Input::get('weights');
        $ship_fors = \Input::get('ship_fors');

        foreach ($lengths as $event_id => $length) {
            $event = Models\Batch\Event::where('id', '=', $event_id)->firstOrFail();

            if ($event->shipment_number != null) {
                continue;
            }

            $width = $widths[$event_id];
            $height = $heights[$event_id];
            $weight = $weights[$event_id];
            $ship_for = $ship_fors[$event_id];

            $event->ship_l = $length ? $length : null;
            $event->ship_w = $width ? $width : null;
            $event->ship_h = $height ? $height : null;
            $event->ship_weight = $weight ? $weight : null;
            $event->ship_for = $ship_for ? $ship_for : null;

            $event->save();
        }

        return \Redirect::to('/tools/shipments');
    }

    public function postShip()
    {
        $current_shipment = Models\Batch\Event::whereNotNull('shipment_number')->orderBy('shipment_number', 'DESC')->first();
        $current_shipment_number = -1;
        if ($current_shipment) {
            $current_shipment_number = $current_shipment->shipment_number;
        }
        $current_shipment_number++;

        foreach (Models\Batch::Loaded()->events as $event) {
            if ($event->ship_ready && $event->shipment_number === null) {
                $event->shipment_number = $current_shipment_number;
                $event->save();
            }
        }

        return \Redirect::to('/tools/shipments');
    }

    public function getCsv()
    {
        $shipment_number = \Input::get('number');

        $events = Models\Batch\Event::where('shipment_number', '=', $shipment_number)->get();

        $csv_lines = [];
        foreach ($events as $event) {
            $name = $event->ship_name;
            $company = $event->ship_company;

            if (!$name) {
                $name = $company;
            } else if (!$company) {
                $company = $name;
            }

            $csv_lines[] = implode(',', [
                $name, $company,
                'US',
                $event->ship_address_1,
                $event->ship_address_2,
                '',
                $event->ship_city,
                $event->ship_state,
                $event->ship_postal,
                '8006077763',
                '',
                $event->ship_is_residential ? '1' : '0',
                '',
                '2',
                '',
                $event->ship_weight,
                $event->ship_l, $event->ship_w, $event->ship_h,
                'LB',
                '', '', '',
                '50',
                '3',
                '',
                '1',
                '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''
            ]);
        }

        $response = \Response::make(implode("\n", $csv_lines), 200);
        $response->header('Content-type', 'text/csv');
        $response->header('Content-Disposition', 'attachment;filename="cd_ship_bat_'.$shipment_number.'_'.time().'.csv"');
        return $response;
    }
}