<?php
namespace CodeDay\Clear\Controllers\Manage\DayOf;

use CodeDay\Clear\Models;
use CodeDay\Clear\Services;

class CallParentsController extends \Controller {

    public function getIndex()
    {
        return \View::make('dayof/call_parents');
    }

    public function postIndex()
    {
        $to = \Input::get('to');
        $message = \Input::get('message');

        $event = Models\Batch\Event::where('id', '=', $to)->firstOrFail();
        $this->checkAccess($event);

        $call = new Models\Batch\Event\Call;
        $call->batches_event_id = $to;
        $call->transcript = $message;
        $call->creator_username = Models\User::me()->username;
        $call->save();

        $twiml = '<Response><Say voice="alice" loop="3">'.$call->full_transcript.'</Say></Response>';

        Services\Phone::callEventParents($event, $twiml, Models\Batch\Event\Call::ExternalNumber);

        \Session::flash('status', 'Call enqued.');
        return \Redirect::to('/dayof/call-parents');
    }


    private function checkAccess($event)
    {
        if (Models\User::me()->username != $event->manager_username
            && Models\User::me()->username != $event->evangelist_username
            && !$event->isUserAllowed(Models\User::me())
            && !Models\User::me()->is_admin) {
            \App::abort(401);
        }
    }
}