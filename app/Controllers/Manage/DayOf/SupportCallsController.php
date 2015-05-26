<?php
namespace CodeDay\Clear\Controllers\Manage\DayOf;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class SupportCallsController extends \Controller {
    public function getIndex()
    {
        return \View::make('dayof/support_calls');
    }

    public function postCall()
    {
        $call = Models\Batch\Event\SupportCall::where('call_sid', '=', \Input::get('sid'))->firstOrFail();

        Services\Telephony\Voice::connectPhones(
            Models\User::me()->phone,
            $call->caller,
            Models\Batch\Event\Call::ExternalNumber
        );

        \Session::flash('status_message', 'Calling you...');
        return \Redirect::to('/dayof/support-calls');
    }
}