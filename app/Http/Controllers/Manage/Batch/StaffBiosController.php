<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;

class StaffBiosController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        $batchId = Models\Batch::Managed()->id;
        $staffInfo = [];
        foreach (Models\Batch::Managed()->events as $event) {
            if (!$event->manager_username || !$event->manager) continue;

            $previouslyManagedEvents = Models\Batch\Event::where('manager_username', '=', $event->manager_username)->where('batch_id', '!=', $batchId)->get();
            $previouslySubuserEvents = Models\User\Grant::where('username', '=', $event->manager_username)->get();
            $previouslyAttendedEvents = Models\Batch\Event\Registration::where('email', '=', $event->manager->email)->where('type', '!=', 'volunteer')->get();

            $staffInfo[$event->manager->username] = (object)[
                'user' => $event->manager,
                'previously_managed' => $previouslyManagedEvents,
                'previously_subuser' => $previouslySubuserEvents,
                'previously_attended' => $previouslyAttendedEvents,
            ];
        }
        return \View::make('batch/bios', ['users' => $staffInfo]);
    }
} 
