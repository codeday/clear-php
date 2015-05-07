<?php
namespace CodeDay\Clear\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class Registrations extends ApiController {
  public function getRegistration()
  {
    $this->requirePermission(['admin']);
    return json_encode(ModelContracts\Registration::Model(\Route::input('registration'), $this->permissions));
  }

  public function getByEmail()
  {
    $this->requirePermission(['admin']);
    $registrations = \DB::table('batches_events_registrations')->orderBy('created_at', 'desc')->where('email', \Input::get('email'))->get();
    $latest = $registrations[0];
    // unset($registrations[0]);
    return json_encode(["latest_registration" => $latest, "all_registrations" => $registrations]);
  }
}
