<?php
namespace CodeDay\Clear\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class Registrations extends ApiController {
  public function getRegistration()
  {
    return json_encode(ModelContracts\Registration::Model(\Route::input('registration'), $this->permissions));
  }

  public function getRegistrationByS5InviteCode()
  {
    $registration = Models\Batch\Event\Registration::where('s5_invite_code', '=', \Route::input('s5_invite'))->first();
    return json_encode(ModelContracts\Registration::Model($registration, $this->permissions));
  }
}
