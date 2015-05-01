<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;
use \CodeDay\Clear\ModelContracts;
use \Carbon\Carbon;

class TasksController extends \Controller {

    public function getIndex()
    {
        return \View::make('tools/tasks');
    }

    public function postSendpreevent()
    {
        $batch = Models\Batch::Managed();
        if ($batch->preevent_email_sent_at !== null) {
            return \App::abort(403);
        }
        $batch->preevent_email_sent_at = Carbon::now();
        $batch->save();

        foreach (Models\Batch::Managed()->events as $event) {
            if ($event->allow_registrations_calculated) {
                try {
                    // Send participant email
                    Services\Email::SendToEvent(
                        'CodeDay ' . $event->name, $event->webname . '@codeday.org',
                        $event, 'attendees',
                        'CodeDay is Shortly Upon Us',
                        \View::make('emails/preevent_text'),
                        \View::make('emails/preevent_html'),
                        [
                            'me' => Models\User::me(),
                            'event' => ModelContracts\Event::Model($event)
                        ]
                    );

                    // Send venue email
                    if ($event->venue_contact_email) {
                        Services\Email::SendOnQueue(
                            'CodeDay '.$event->name, $event->webname.'@codeday.org',
                            $event->venue_contact_first_name.' '.$event->venue_contact_last_name, $event->venue_contact_email,
                            'Information for CodeDay at '.$event->venue_name,
                            null,
                            \View::make('emails/preevent_venue_html', ['event' => $event])
                        );
                    }

                    // Send staff emails
                    $staff = [];
                    if ($event->manager_username) {
                        $staff[] = ['name' => $event->manager->name, 'email' => $event->manager->email];
                        $staff[] = ['name' => $event->manager->name, 'email' => $event->manager->internal_email];
                    }
                    if ($event->evangelist_username) {
                        $staff[] = ['name' => $event->evangelist->name, 'email' => $event->evangelist->email];
                        $staff[] = ['name' => $event->evangelist->name, 'email' => $event->evangelist->internal_email];
                    }
                    foreach ($event->grants as $grant) {
                        $staff[] = ['name' => $grant->name, 'email' => $grant->email];
                        $staff[] = ['name' => $grant->name, 'email' => $grant->internal_email];
                    }

                    foreach ($staff as $member) {
                        Services\Email::SendOnQueue(
                            'CodeDay '.$event->name, $event->webname.'@codeday.org',
                            $member['name'], $member['email'],
                            'CodeDay is Shortly Upon Us',
                            null,
                            \View::make('emails/preevent_staff_html', ['event' => $event])
                        );
                    }
                } catch (\Exception $ex) {}
            }
        }

        \Session::flash('status_message', 'Email enqueued');

        return \Redirect::to('/tools/tasks');
    }

    public function postSendreminder()
    {
        $batch = Models\Batch::Managed();
        if ($batch->reminder_email_sent_at !== null) {
            return \App::abort(403);
        }
        $batch->reminder_email_sent_at = Carbon::now();
        $batch->save();

        foreach (Models\Batch::Managed()->events as $event) {
            if ($event->allow_registrations_calculated) {
                try {
                    Services\Email::SendToEvent(
                        'CodeDay '.$event->name, $event->webname.'@codeday.org',
                        $event, 'attendees',
                        'Reminder: Your Registration for CodeDay',
                        \View::make('emails/reminder_text'),
                        \View::make('emails/reminder_html'),
                        [
                            'me' => Models\User::me(),
                            'event' => ModelContracts\Event::Model($event)
                        ]
                    );
                } catch (\Exception $ex) {}
            }
        }

        \Session::flash('status_message', 'Email enqueued');

        return \Redirect::to('/tools/tasks');
    }

    public function postSendvenuereminder()
    {
        $batch = Models\Batch::Managed();
        if ($batch->venue_reminder_email_sent_at !== null) {
            return \App::abort(403);
        }
        $batch->venue_reminder_email_sent_at = Carbon::now();
        $batch->save();

        foreach (Models\Batch::Managed()->events as $event) {
            if ($event->allow_registrations_calculated && $event->venue_contact_email) {
                try {
                    Services\Email::SendOnQueue(
                        'CodeDay '.$event->name, $event->webname.'@codeday.org',
                        $event->venue_contact_first_name.' '.$event->venue_contact_last_name, $event->venue_contact_email,
                        'Reminder: CodeDay at '.$event->venue_name,
                        null,
                        \View::make('emails/reminder_venue_html', ['event' => $event])
                    );
                } catch (\Exception $ex) {}
            }
        }

        \Session::flash('status_message', 'Email enqueued');

        return \Redirect::to('/tools/tasks');
    }

    public function postSendvenuepostevent()
    {
        $batch = Models\Batch::Managed();
        if ($batch->venue_postevent_email_sent_at !== null) {
            return \App::abort(403);
        }
        $batch->venue_postevent_email_sent_at = Carbon::now();
        $batch->save();

        foreach (Models\Batch::Managed()->events as $event) {
            if ($event->allow_registrations_calculated && $event->venue_contact_email) {
                try {
                    Services\Email::SendOnQueue(
                        'CodeDay '.$event->name, $event->webname.'@codeday.org',
                        $event->venue_contact_first_name.' '.$event->venue_contact_last_name, $event->venue_contact_email,
                        'How did we do?',
                        null,
                        \View::make('emails/postevent_venue_html', ['event' => $event])
                    );
                } catch (\Exception $ex) {}
            }
        }

        \Session::flash('status_message', 'Email enqueued');

        return \Redirect::to('/tools/tasks');
    }
}