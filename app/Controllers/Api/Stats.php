<?php
namespace CodeDay\Clear\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class Stats extends ApiController {
  public function getIndex(){
    $this->requirePermission(["internal"]);
    $registrations = Models\Batch\Event\Registration::count();

    return json_encode([
      "registrations_all_time" => $registrations
    ]);
  }
}
