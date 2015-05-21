<?php
namespace CodeDay\Clear\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;

class RevenueController extends \Controller {

    public function getIndex()
    {
        return \View::make('batch/revenue');
    }
} 