<?php

namespace CodeDay\Clear\Commands\Jobs;

use \Carbon\Carbon;
use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class SendAttendeeRemindersJob {
    public $interval = 'always';
    
    public function fire()
    {
        return;
        foreach (Models\Batch::LoadedAll() as $batch) {
            foreach ($batch->events as $event) {
                foreach($event->schedule as $day) {
                    foreach($day as $activity){
                        $adjustedBatchTimezone = $batch->starts_at->setTimezone($event->batch->timezone)->hour(12);

                        $activityTime = $adjustedBatchTimezone->addMinutes($activity->time * 60);
                        $notifyTime = $activityTime->subMinutes(30);

                        $now = Carbon::now()->setTimezone($event->batch->timezone);

                        if($notifyTime->diffInMinutes($now) == 0 && $activity->type == "workshop") {
                            Services\Notifications::SendNotificationsForActivity($activity, $event);
                        }
                    }
                }
            }
        }
    }
}
