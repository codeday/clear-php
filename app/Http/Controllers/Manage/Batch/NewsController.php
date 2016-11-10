<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;

class NewsController extends \CodeDay\Clear\Http\Controller {
    public function getIndex()
    {
        return \View::make('batch/news');
    }

    public function postIndex()
    {
        $batch = Models\Batch::Managed();
        $batch->news = \Input::get('news');
        $batch->save();

        \Session::flash('status_message', 'News updated.');

        return \Redirect::to('/batch/news');
    }
} 
