<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class AttendeeController extends \Controller {

    public function getIndex()
    {
        $search = \Input::get('search');

        $attendees = null;
        if ($search) {
            $attendees = Models\Batch\Event\Registration::where('first_name', 'LIKE', '%'.$search.'%')
                ->orWhere('last_name', 'LIKE', '%'.$search.'%')
                ->orWhere('email', 'LIKE', '%'.$search.'%')
                ->get();
        }

        return \View::make('tools/attendee', [
            'attendees' => $attendees,
            'search' => $search
        ]);
    }
} 