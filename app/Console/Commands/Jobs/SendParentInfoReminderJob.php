<?php

namespace CodeDay\Clear\Commands\Jobs;

use \Carbon\Carbon;
use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class SendParentInfoReminderJob {
    public $interval = '1 week';
    
    public function fire()
    {
        // Only send the reminders if we're within three weeks of the event
        if (Carbon::now()->subWeeks(3)->lte(Models\Batch::Loaded()->starts_at)
            && !Models\Batch::Loaded()->starts_at->isPast()) {

            $registrationsMissingParentInfo = Models\Batch\Event\Registration
                ::select('batches_events_registrations.*')
                ->leftJoin('batches_events', 'batches_events.id', '=', 'batches_events_registrations.batches_event_id')
                ->where('batches_events.batch_id', '=', Models\Batch::Loaded()->id)
                ->where(function($where) {
                    $where->whereNull('parent_email')
                          ->where('parent_no_info', '=', false);
                })
                ->where('batches_events_registrations.type', '=', 'student')
                ->get();

            foreach ($registrationsMissingParentInfo as $registration) {
                Services\Email::SendOnQueue(
                    'CodeDay '.$registration->event->name, $registration->event->webname.'@codeday.org',
                    $registration->name, $registration->email,
                    'Reminder: We need your parent info!', null,
                    \View::make('emails/nags/parent_info_html', ['registration' => $registration]),
                    false
                );
            }
        }
    }
}
