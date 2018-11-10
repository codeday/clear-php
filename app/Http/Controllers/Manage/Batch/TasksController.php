<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;
use \CodeDay\Clear\ModelContracts;
use \Carbon\Carbon;

class TasksController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('batch/tasks');
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
            'Reminder: {{ event.name }}',
            null,
            \View::make('emails/preevent/reminders/venue_html')
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
            'Tyler Menezes', 'tylermenezes@srnd.org',
            Models\Batch::Managed(), 'venues',
            'How did we do?',
            null,
            \View::make('emails/postevent/venue_html')
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
            'John Peter', '{{ event.webname }}@codeday.org',
            Models\Batch::Managed(), 'attendees',
            '#CodeDay #SoLastSeason',
            null,
            \View::make('emails/postevent/survey_html'),
            []
        );

        Services\Email::SendToBatch(
            'John Peter', '{{ event.webname }}@codeday.org',
            Models\Batch::Managed(), 'mentors',
            '#CodeDay #SoLastSeason',
            null,
            \View::make('emails/postevent/survey_html'),
            []
        );

        Services\Email::SendToBatch(
            'John Peter', '{{ event.webname }}@codeday.org',
            Models\Batch::Managed(), 'event-staff',
            '#CodeDay #SoLastSeason',
            null,
            \View::make('emails/postevent/survey_html'),
            []
        );

        Services\Email::SendToBatch(
            'Tyler Menezes', 'tylermenezes@srnd.org',
            Models\Batch::Managed(), 'rms',
            'How did I do?',
            null,
            \View::make('emails/postevent/rm')
        );


        \Session::flash('status_message', 'Email enqueued');

        return \Redirect::to('/batch/tasks');
    }
}
