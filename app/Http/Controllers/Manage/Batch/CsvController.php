<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class CsvController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return Services\Registration::GetCsvMultiple(Models\Batch::Managed()->events);
    }
} 
