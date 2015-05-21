<?php
namespace CodeDay\Clear\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;

class ChangeController extends \Controller {

    public function getIndex()
    {
        if (\Input::get('id')) {
            Models\Batch::find(\Input::get('id'))->manage();
            return \Redirect::to('/batch');
        } else {
            return \View::make('batch/change');
        }
    }
}