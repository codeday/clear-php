<?php
namespace CodeDay\Clear\Http\Controllers\Manage\DayOf;

use JBDemonte\Barcode;
use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;
use \CodeDay\Clear\Services;

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
            Services\Notifications::SendCheckinNotification($attendee);
        } else {
            $attendee->checked_in_at = null;
            $attendee->save();
            \Event::fire('registration.checkout', \DB::table('batches_events_registrations')->where('id', '=', $attendee_id)->get()[0]);
        }

        return json_encode((object)['status' => 200, 'notes' => $attendee->notes, 'equipment' => count($attendee->equipment) > 0]);
    }

    public function getConfiguration()
    {
        $configuration = json_encode((object)[
            'eventId' => getDayOfEvent()->id,
            'eventName' => getDayOfEvent()->fullName,
            'token' => Models\User::me()->token
        ]);

        // Draw the configuration barcode
        $im = \imagecreate(130, 130); 
        $black = \imagecolorallocate($im, 0, 0, 0);
        $white  = \imagecolorallocate($im, 248, 248, 250);

        imagefilledrectangle($im, 0, 0, 200, 200, $white);
        Barcode::gd($im, $black, 65, 65, 0, "datamatrix", $configuration, 2);

        $response = \Response::make('', 200);
        // Images bigger than ~100x100px will cause PHP to flush the output buffer, so we need to send a header now
        // but images smaller than that won't cause any output buffering, so we need to return a response with the
        // proper header so it doesn't get overridden.
        //
        // This wouldn't be a problem if imagepng would return instead of echoing.
        header('Content-type: image/png');
        header('Cache-control: public,max-age=604800,no-transform');
        $response->header('Content-Type', 'image/png');
        $response->header('Cache-control', 'public,max-age=604800,no-transform');

        \imagepng($im);
        return $response;

    }
}
