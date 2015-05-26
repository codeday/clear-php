<?php

namespace CodeDay\Clear\Commands\Jobs;

use \Carbon\Carbon;
use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class SendRMEventStatusJob {
    public $interval = '1 day';
    public function fire()
    {
        if (Models\Batch::Loaded()->starts_at->isPast()) { return; } // Don't send emails once the event has started

        $interval = (Models\Batch::Loaded()->starts_at->addWeeks(-2)->isPast()) ? 'daily' : 'weekly';
        $cronjob = Models\Cronjob::where('class', '=', '\\CodeDay\\Clear\\Commands\\Jobs\\'.__CLASS__)->first();
        $last_run = $cronjob ? $cronjob->updated_at : Carbon::createFromTimestamp(0);

        if ($interval === 'weekly' && ($last_run->addDays(5)->isFuture() || Carbon::now()->dayOfWeek != 1)) {
            echo "Skipping - set to run weekly; too recent or not current Monday\n";
            return;
        }

        $emailEvents = Models\Batch\Event
            ::select('batches_events.*')
            ->where('batch_id', '=', Models\Batch::Loaded()->id)
            ->whereNotNull('manager_username')
            ->groupBy('manager_username')
            ->get();

        $leaderboard = iterator_to_array(Models\Batch\Event
            ::where('batch_id', '=', Models\Batch::Loaded()->id)
            ->get()
        );

        usort($leaderboard, function($a, $b) {
            return count($b->registrations_this_week) - count($a->registrations_this_week);
        });

        foreach ($emailEvents as $event) {
            echo "Sending email to ".$event->manager->name."\n";
            Services\Email::SendOnQueue(
                'StudentRND Happy Robot', 'contact@studentrnd.org',
                $event->manager->name, $event->manager->username.'@studentrnd.org',
                ucfirst($interval).' Event Update For '.date('l, F j'),
                null,
                \View::make('emails/rm_event_status_html', [
                    'user' => $event->manager,
                    'leaderboard' => $leaderboard
                ]),
                true
            );
        }
    }
}