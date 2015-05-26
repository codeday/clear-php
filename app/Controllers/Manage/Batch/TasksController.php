<?php
namespace CodeDay\Clear\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;
use \CodeDay\Clear\ModelContracts;
use \Carbon\Carbon;

class TasksController extends \Controller {

    public function getIndex()
    {
        return \View::make('batch/tasks');
    }

    public function postSendpreevent()
    {
        $batch = Models\Batch::Managed();
        if ($batch->preevent_email_sent_at !== null) {
            return \App::abort(403);
        }
        $batch->preevent_email_sent_at = Carbon::now();
        $batch->save();

        // Send the participant email
        Services\Email::SendToBatch(
            'CodeDay {{ event.name }}', '{{ event.webname }}@codeday.org',
            Models\Batch::Managed(), 'attendees',
            'CodeDay is Shortly Upon Us',
            \View::make('emails/preevent_text'),
            \View::make('emails/preevent_html')
        );

        // Send the staff email
        Services\Email::SendToBatch(
            'CodeDay {{ event.name }}', '{{ event.webname }}@codeday.org',
            Models\Batch::Managed(), 'event-staff',
            'CodeDay Looms',
            null,
            \View::make('emails/preevent_staff_html')
        );

        // Send the venue email
        Services\Email::SendToBatch(
            'CodeDay {{ event.name }}', '{{ event.webname }}@codeday.org',
            Models\Batch::Managed(), 'venues',
            'Information for CodeDay {{ event.name }}',
            null,
            \View::make('emails/preevent_venue_html')
        );

        \Session::flash('status_message', 'Email enqueued');

        return \Redirect::to('/batch/tasks');
    }

    public function postSendreminder()
    {
        $batch = Models\Batch::Managed();
        if ($batch->reminder_email_sent_at !== null) {
            return \App::abort(403);
        }
        $batch->reminder_email_sent_at = Carbon::now();
        $batch->save();

        // Send the participant email
        Services\Email::SendToBatch(
            'CodeDay {{ event.name }}', '{{ event.webname }}@codeday.org',
            Models\Batch::Managed(), 'attendees',
            'Reminder: Your Registration for CodeDay',
            \View::make('emails/reminder_text'),
            \View::make('emails/reminder_html')
        );

        // Send the parent email
        Services\Email::SendToBatch(
            'CodeDay {{ event.name }}', '{{ event.webname }}@codeday.org',
            Models\Batch::Managed(), 'parents',
            'Reminder: CodeDay Is This Weekend',
            null,
            \View::make('emails/parent_reminder_html')
        );

        \Session::flash('status_message', 'Email enqueued');

        return \Redirect::to('/batch/tasks');
    }

    public function postSendvenuereminder()
    {
        $batch = Models\Batch::Managed();
        if ($batch->venue_reminder_email_sent_at !== null) {
            return \App::abort(403);
        }
        $batch->venue_reminder_email_sent_at = Carbon::now();
        $batch->save();

        Services\Email::SendToBatch(
            'CodeDay {{ event.name }}', '{{ event.webname }}@codeday.org',
            Models\Batch::Managed(), 'venues',
            'Reminder: CodeDay {{ event.name }}',
            null,
            \View::make('emails/reminder_venue_html')
        );

        \Session::flash('status_message', 'Email enqueued');

        return \Redirect::to('/batch/tasks');
    }

    public function postSendvenuepostevent()
    {
        $batch = Models\Batch::Managed();
        if ($batch->venue_postevent_email_sent_at !== null) {
            return \App::abort(403);
        }
        $batch->venue_postevent_email_sent_at = Carbon::now();
        $batch->save();


        Services\Email::SendToBatch(
            'CodeDay {{ event.name }}', '{{ event.webname }}@codeday.org',
            Models\Batch::Managed(), 'venues',
            'How did we do?',
            null,
            \View::make('emails/postevent_venue_html')
        );

        \Session::flash('status_message', 'Email enqueued');

        return \Redirect::to('/batch/tasks');
    }

    public function postSendsurvey()
    {
        $batch = Models\Batch::Managed();
        if ($batch->survey_email_sent_at !== null) {
            return \App::abort(403);
        }
        $batch->survey_email_sent_at = Carbon::now();
        $batch->save();

        Services\Email::SendToBatch(
            'CodeDay {{ event.name }}', '{{ event.webname }}@codeday.org',
            Models\Batch::Managed(), 'attendees',
            'Making CodeDay Better',
            null,
            \View::make('emails/survey_html'),
            [
                'survey' => \Input::get('survey')
            ]
        );

        \Session::flash('status_message', 'Email enqueued');

        return \Redirect::to('/batch/tasks');
    }
}
