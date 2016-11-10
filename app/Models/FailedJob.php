<?php
namespace CodeDay\Clear\Models;

use Illuminate\Database\Eloquent;

class FailedJob extends \Eloquent {
    protected $table = 'failed_jobs';

    public function getDates()
    {
        return ['failed_at'];
    }
}