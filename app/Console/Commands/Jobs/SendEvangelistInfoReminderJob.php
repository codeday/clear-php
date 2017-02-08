<?php

namespace CodeDay\Clear\Commands\Jobs;

use \Carbon\Carbon;
use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class SendEvangelistInfoReminderJob {
    public $interval = '3 days';
    public function fire()
    {
        if (Carbon::now()->gte(Models\Batch::Loaded()->starts_at->addWeeks(-3))) {
            $evangelistsWithMissingInformation = Models\User
                ::select('users.*')
                ->leftJoin('batches_events', 'batches_events.evangelist_username', '=', 'users.username')
                ->where('batches_events.batch_id', '=', Models\Batch::Loaded()->id)
                ->whereNull('users.phone')
                ->groupBy('batches_events.evangelist_username')
                ->get();

            foreach ($evangelistsWithMissingInformation as $evangelist) {
                echo "Queuing reminder for {$evangelist->name}\n";
                Services\Email::SendOnQueue(
                    'StudentRND Robot', 'contact@srnd.org',
                    $evangelist->name, $evangelist->email,
                    'Phone Number Required For CodeDay',
                    null,
                    \View::make('emails/nags/evangelist_info_html', ['user' => $evangelist]),
                    false
                );
            }
        } else {
            echo "Skipping reminder emails, too early.\n";
        }
    }
}
