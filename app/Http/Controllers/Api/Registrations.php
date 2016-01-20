<?php
namespace CodeDay\Clear\Http\Controllers\Api;

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
    $registrations = Models\Batch\Event\Registration::orderBy('created_at', 'desc')->where('email', \Route::input('email'))->get();

    if (count($registrations) === 0) {
      return json_encode(['latest_registration' => null, 'all_registrations' => []]);
    }

    $latest = $registrations[0];
    return json_encode([
        "latest_registration" => ModelContracts\Registration::Model($latest),
        "all_registrations" => ModelContracts\Registration::Collection($registrations)
    ]);
  }
}
