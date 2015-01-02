<?php
namespace CodeDay\Clear\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class Events extends ApiController {
    public function getIndex()
    {
        return json_encode(ModelContracts\Event::Collection(Models\Batch\Event::all(), $this->permissions));
    }

    public function getEvent()
    {
        return json_encode(ModelContracts\Event::Model(\Route::input('event'), $this->permissions));
    }
} 
