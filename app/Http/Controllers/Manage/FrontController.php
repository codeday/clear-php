<?php
namespace CodeDay\Clear\Http\Controllers\Manage;

use \CodeDay\Clear\Services;
use \CodeDay\Clear\Models;
use \Carbon\Carbon;

class FrontController extends \CodeDay\Clear\Http\Controller {
    public function getPlugin()
    {
        return \View::make('front/plugin');
    }

    public function getAttendee()
    {
        $attendee = null;
        if (\Input::get('email')) {
            $attendee = Models\Batch\Event\Registration
                ::where('email', '=', \Input::get('email'))
                ->orWhere('parent_email', '=', \Input::get('email'))
                ->orderBy('created_at', 'DESC')
                ->first();
        }

        if (isset($attendee)) {
            return \View::make('front/attendee', [
                'registration' => $attendee,
                'event' => $attendee->event,
                'csrf' => \csrf_field()
            ]);
        } else {
            return \View::make('front/no-attendee');
        }
    }
}
