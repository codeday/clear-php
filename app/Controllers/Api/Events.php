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

    public function getManagedBy()
    {
        $this->requirePermission(['internal']);
        $user = Models\User::where('username', '=', \Route::input('username'))->get();
        return json_encode($user);
    }
}
