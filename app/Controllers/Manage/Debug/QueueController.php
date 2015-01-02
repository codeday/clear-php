<?php
namespace CodeDay\Clear\Controllers\Manage\Debug;

use \CodeDay\Clear\Models;

class QueueController extends \Controller {

    public function getIndex()
    {

    }

    public function getFailed()
    {
        $failed_jobs = Models\FailedJob::orderBy('failed_at', 'DESC')->get();
        return \View::make('debug/queue-failed', ['jobs' => $failed_jobs]);
    }

    public function postRemovefailed()
    {
        Models\FailedJob::where('id', '=', \Input::get('id'))->first()->delete();
        return \Redirect::to('/debug/queue/failed');
    }
} 