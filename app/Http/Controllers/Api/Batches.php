<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class Batches extends ApiController {
    public function getIndex()
    {
        return json_encode(ModelContracts\Batch::Collection(Models\Batch::all(), $this->permissions));
    }

    public function getCurrent()
    {
        return json_encode(ModelContracts\Batch::Model(Models\Batch::Loaded(), $this->permissions));
    }

    public function getEvents()
    {
        return json_encode(ModelContracts\Event::Collection(Models\Batch\Event::where('batch_id', '=', \Route::input('batch')->id)->get(), $this->permissions));
    }
}
