<?php
namespace CodeDay\Clear\Http\Controllers\Manage\DayOf;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;
use \Carbon\Carbon;

class BreakController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        $onBreak = Models\Batch\Event\Registration::whereNotNull('break_started_at')->orderBy('break_started_at', 'ASC')->get();
        $offBreak = Models\Batch\Event\Registration::whereNull('break_started_at')->get();
        return \View::make('dayof/break', ['on_break' => $onBreak, 'off_break' => $offBreak]);
    }

    public function postStart()
    {
        $reg = Models\Batch\Event\Registration::where('id', '=', \Input::get('id'))->firstOrFail();
        $reg->phone = \Input::get('phone');
        $reg->break_started_at = Carbon::now();
        $reg->save();

        return \Redirect::to('/dayof/break');
    }

    public function postEnd()
    {
        $reg = Models\Batch\Event\Registration::where('id', '=', \Input::get('id'))->firstOrFail();
        $reg->break_started_at = null;
        $reg->save();

        return \Redirect::to('/dayof/break');
    }
}
