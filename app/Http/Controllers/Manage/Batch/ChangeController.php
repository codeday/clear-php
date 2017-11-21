<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;

class ChangeController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        if (\Input::get('id')) {
            Models\Batch::find(\Input::get('id'))->manage();
            return \Redirect::to('/batch');
        } else {
            \View::share('all_batches', Models\Batch::orderBy('starts_at', 'ASC')->get());
            return \View::make('batch/change');
        }
    }
}
