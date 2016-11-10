<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

// This method requires authentication with a `token`
class UserController extends ApiController {
  public $requiresApplication = false;

  public function __construct() {
      if (\Input::get('token')) {
        $user = Models\User::fromToken(\Input::get('token'));

        if (!isset($user)) {
            \App::abort(403);
        }

        $this->permissions = ['admin'];
        $this->application = true;
      } else {
        \App::abort(403);
      }
  }

  public function getManagedEvents(){
    return json_encode(ModelContracts\Event::Collection($user->current_managed_events, $this->permissions));
  }
}
