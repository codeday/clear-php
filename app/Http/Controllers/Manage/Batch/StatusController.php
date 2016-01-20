<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;

class StatusController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('batch/status');
    }
} 