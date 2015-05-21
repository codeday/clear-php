<?php
namespace CodeDay\Clear\Controllers\Manage\DayOf;

use CodeDay\Clear\Models;
use CodeDay\Clear\Services;

class SendSmsController extends \Controller {

    public function getIndex()
    {
        return \View::make('dayof/send_sms');
    }

    public function postIndex()
    {
        $to = \Input::get('to');
        $message = \Input::get('message');

        if (substr($to, 0, 2) === 'u:') {
            $user = Models\User::where('username', '=', substr($to, 2))->firstOrFail();
            Services\Sms::sendToUser($user, $message);
        } else {
            Services\Sms::sendToList($to, $message);
        }

        \Session::flash('status', 'SMS enqued.');
        return \Redirect::to('/dayof/send-sms');
    }

}