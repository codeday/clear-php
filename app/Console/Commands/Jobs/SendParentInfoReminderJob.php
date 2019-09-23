<?php

namespace CodeDay\Clear\Commands\Jobs;

use \Carbon\Carbon;
use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class SendParentInfoReminderJob {
    public $interval = '1 week';
    
    public function fire()
    {
        foreach (Models\Batch::LoadedAll() as $batch) {
            // Only send the reminders if we're within three weeks of the event
            if (Carbon::now()->subWeeks(3)->lte($batch)
                && !$batch->starts_at->isPast()) {

                $registrationsMissingParentInfo = Models\Batch\Event\Registration
                    ::select('batches_events_registrations.*')
                    ->leftJoin('batches_events', 'batches_events.id', '=', 'batches_events_registrations.batches_event_id')
                    ->where('batches_events.batch_id', '=', $batch->id)
                    ->where('batches_events.venue_country', '!=', 'KE')
                    ->where(function($where) {
                        $where->whereNull('parent_email')
                            ->where('age', '<', 21); // "Minor" varies by state, and we can't calculate until we have the registrations, but we
                                                    // can save some time by not pulling obviously-adults.
                    })
                    ->where('batches_events_registrations.type', '=', 'student') // TODO(@tylermenezes): Check if ->requires_emergency_info instead
                    ->get();

                foreach ($registrationsMissingParentInfo as $registration) {
                    if (!$registration->is_minor) continue;
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
}
