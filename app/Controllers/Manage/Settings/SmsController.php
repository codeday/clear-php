<?php
namespace CodeDay\Clear\Controllers\Manage\Settings;

use \CodeDay\Clear\Models;

class SmsController extends \Controller {

    public function getIndex()
    {
        return \View::make('settings/sms', ['sms' => Models\EvangelistSms::orderBy('hours_offset')->get()]);
    }

    public function postNew()
    {
        $sms = new Models\EvangelistSms;
        $sms->hours_offset = \Input::get('hours_offset');
        $sms->content = \Input::get('content');
        $sms->save();

        \Session::flash('status_message', 'SMS added');

        return \Redirect::to('/settings/sms');
    }

    public function postDelete()
    {
        Models\EvangelistSms::where('id', '=', \Input::get('id'))->firstOrFail()->delete();
        \Session::flash('status_message', 'SMS removed');
        return \Redirect::to('/settings/sms');
    }
} 