<?php
namespace CodeDay\Clear\Http\Controllers\Manage\DayOf;

use JBDemonte\Barcode;
use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;
use \CodeDay\Clear\Services;

class EquipmentController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('dayof/equipment');
    }

    public function postCheckin()
    {
        $event = getDayOfEvent();
        $attendee_id = \Input::get('id');
        $equipment_id = \Input::get('equipment');

        $attendee = Models\Batch\Event\Registration::where('id', '=', $attendee_id)->firstOrFail();
        $equipment = Models\Batch\Event\Registration\Equipment::where('id', '=', $equipment_id)->firstOrFail();
        if (!$event || $event->id !== $attendee->batches_event_id || $equipment->batches_events_registration_id !== $attendee->id) {
            \App::abort(404);
        }
        $equipment->delete();
        return json_encode((object)['status' => 200]);
    }

    public function postCheckout()
    {
        $event = getDayOfEvent();
        $attendee_id = \Input::get('id');
        $equipment_id = \Input::get('equipment');

        $attendee = Models\Batch\Event\Registration::where('id', '=', $attendee_id)->firstOrFail();
        if (!$event || $event->id !== $attendee->batches_event_id) {
            \App::abort(404);
        }

        $equipment = new Models\Batch\Event\Registration\Equipment;
        $equipment->batches_events_registration_id = $attendee->id;
        $equipment->equipment_id = $equipment_id;
        $equipment->save();

        return json_encode((object)['status' => 200, 'id' => $equipment->id]);
    }
}
