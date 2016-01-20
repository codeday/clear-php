<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class Stats extends ApiController {
  public function getIndex(){
    $this->requirePermission(["internal"]);
    $registrations = Models\Batch\Event\Registration::count();
    $events = Models\Batch\Event::count();
    $batches = Models\Batch::count();

    return json_encode([
      "registrations_all_time" => $registrations,
      "events_all_time" => $events,
      "batches_all_time" => $batches
    ]);
  }
}
