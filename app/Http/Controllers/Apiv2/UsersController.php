<?php
namespace CodeDay\Clear\Http\Controllers\Apiv2;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class UsersController extends Apiv2Controller {
    public function getMe()
    {
        $this->setUserOrFail();
        return $this->apiSuccess(ModelContracts\User::Model($this->user, $this->permissions));
    }
}
