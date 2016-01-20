<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Batch;

use CodeDay\Clear\Models;
use CodeDay\Clear\Services;

class SendSmsController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('batch/send_sms');
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
        return \Redirect::to('/batch/send-sms');
    }

}