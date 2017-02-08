<?php

namespace CodeDay\Clear\Commands\Jobs;

use \Carbon\Carbon;
use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class SendRMInfoReminderJob {
    public $interval = '1 day';
    public function fire()
    {
        if (Carbon::now()->gte(Models\Batch::Loaded()->starts_at->addWeeks(-3))) {
            $eventsWithMissingInformation = Models\Batch\Event
                ::where('batch_id', '=', Models\Batch::Loaded()->id)
                ->whereNotNull('manager_username')
                ->where(function($x) {
                    $x->whereNull('ship_address_1')
                        ->orWhere(function($y) {
                            $y->whereNull('venue_contact_email')
                            ->whereNotNull('venue_name');
                        });
                })
                ->groupBy('manager_username')
                ->get();

            foreach ($eventsWithMissingInformation as $event) {
                echo "Queuing reminder for {$event->name}\n";
                Services\Email::SendOnQueue(
                    'StudentRND Robot', 'contact@srnd.org',
                    $event->manager->name, $event->manager->username.'@srnd.org',
                    'Missing Event Info Reminder For '.date('l, F j'),
                    null,
                    \View::make('emails/nags/rm_info_html', ['user' => $event->manager])
                );
            }
        } else {
            echo "Skipping reminder emails, too early.\n";
        }
    }
}
