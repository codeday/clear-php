<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class Application extends ApiController {
    public function getApplication()
    {
        return json_encode(ModelContracts\Application::Model($this->application, $this->permissions));
    }
}
