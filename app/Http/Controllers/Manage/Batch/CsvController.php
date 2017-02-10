<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class CsvController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        $content = Services\Registration::GetCsvMultiple(Models\Batch::Managed()->events);

        return (new \Illuminate\Http\Response($content, 200))
            ->header('Content-type', 'text/csv')
            ->header('Content-disposition', 'attachment;filename=all-attendees-'.time().'.csv');
    }
} 
