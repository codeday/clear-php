<?php
namespace CodeDay\Clear\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;

class DirectoryController extends \Controller {

    public function getIndex()
    {
        return \View::make('batch/directory');
    }
} 