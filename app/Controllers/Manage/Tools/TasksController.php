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

            Services\Email::SendToEvent(
                'CodeDay '.$event->name, $event->webname.'@codeday.org',
                $event, 'attendees',
                'CodeDay is Shortly Upon Us',
                \View::make('emails/preevent_text'),
                null,
                [
                    'me' => Models\User::me(),
                    'event' => ModelContracts\Event::Model($event)
                ]
            );
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

            Services\Email::SendToEvent(
                'CodeDay '.$event->name, $event->webname.'@codeday.org',
                $event, 'attendees',
                'Reminder: Your Registration for CodeDay',
                \View::make('emails/reminder_text'),
                null,
                [
                    'me' => Models\User::me(),
                    'event' => ModelContracts\Event::Model($event)
                ]
            );
        }

        \Session::flash('status_message', 'Email enqueued');

        return \Redirect::to('/tools/tasks');
    }
}