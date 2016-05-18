<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class Registrations extends ApiController {
    public function postIndex()
    {
        $this->requirePermission(['admin']);
        $registration = \Route::input('registration');
        $check = \Input::get('check');
        $allowMissing = \Input::get('allow_missing') ? true : false;

        $hasParent = isset($registration->parent_email) || $registration->parent_no_info;
        $hasWaiver = isset($registration->waiver_pdf_link);

        if ($check === 'in') {
            if ($allowMissing || ($hasParent && $hasWaiver)) {
                $registration->checked_in_at = \Carbon\Carbon::now();
                $registration->save();
                \Event::fire('registration.checkin', \DB::table('batches_events_registrations')->where('id', '=', $attendee_id)->get()[0]);
            }
        } elseif ($check === 'out') {
            $registration->checked_in_at = null;
            $registration->save();
            \Event::fire('registration.checkout', \DB::table('batches_events_registrations')->where('id', '=', $attendee_id)->get()[0]);
        } else {
            \App::abort(405);
        }

        return json_encode((object)[
            'checked_in_at' => $registration->checked_in_at->timestamp,
            'has_parent_info' => $hasParent,
            'has_waiver' => $hasWaiver
        ]);
    }
}
