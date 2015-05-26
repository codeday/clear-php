<?php
namespace CodeDay\Clear\Controllers\Manage\DayOf;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class EmergencyController extends \Controller {
    public function getIndex()
    {
        return \View::make('dayof/emergency/index');
    }

    public function getCall()
    {
        $registration = Models\Batch\Event\Registration::where('id', '=', \Input::get('id'))->firstOrFail();
        if ($registration->batches_event_id !== getDayOfEvent()->id) \App::abort(404);

        return \View::make('dayof/emergency/call', [
            'registration' => $registration,
            'num' => \Input::get('num')
        ]);
    }

    public function postCall()
    {
        $registration = Models\Batch\Event\Registration::where('id', '=', \Input::get('id'))->firstOrFail();
        if ($registration->batches_event_id !== getDayOfEvent()->id) \App::abort(404);

        // Figure out which parent phone we're using
        $number = \Input::get('num') === 'secondary' ?
            $registration->parent_secondary_phone : $registration->parent_phone;

        // Make the call
        Services\Telephony\Voice::connectPhones(
            Models\User::me()->phone,
            $number,
            Models\Batch\Event\Call::ExternalNumber);

        \Session::flash('status_message', 'Calling you...');
        return \Redirect::to('/dayof/emergency');
    }

    public function postRobocall()
    {
        $message = \Input::get('message');

        $event = getDayOfEvent();

        $call = new Models\Batch\Event\Call;
        $call->batches_event_id = $event->id;
        $call->transcript = $message;
        $call->creator_username = Models\User::me()->username;
        $call->save();

        $twiml = '<Response><Say voice="alice" loop="3">'.$call->full_transcript.'</Say></Response>';

        Services\Telephony\Voice::callEventParents($event, $twiml, Models\Batch\Event\Call::ExternalNumber);

        \Session::flash('status_message', 'Call enqued.');
        return \Redirect::to('/dayof/emergency');
    }
}