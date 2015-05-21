<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use CodeDay\Clear\Models;
use CodeDay\Clear\Services;

class SendSmsController extends \Controller {

    public function getIndex()
    {
        return \View::make('tools/send_sms');
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
        return \Redirect::to('/tools/send-sms');
    }

}