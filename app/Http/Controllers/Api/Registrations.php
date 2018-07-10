<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use CodeDay\Clear\Services;
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

    $sparse = (\Input::get('sparse') == "true" ? true : false);
    
    $latest = $registrations[0];
    return json_encode([
        "latest_registration" => ModelContracts\Registration::Model($latest, ["admin"]),
        "all_registrations" => ModelContracts\Registration::Collection($registrations, ["admin"], null, $sparse)
    ]);
  }

  public function postParentInfo()
  {
    $this->requirePermission(['admin']);
    $registration = \Route::input('registration');

    if (\Input::get('age')) {
        $registration->age = \Input::get('age');
        if (!$registration->is_minor) {
            $registration->parent_name = null;
            $registration->parent_email = null;
            $registration->parent_phone = null;
            $registration->parent_secondary_phone = null;
        }
    }

    if (\Input::get('parent_name') || \Input::get('parent_email')) {
        $registration->parent_name = \Input::get('parent_name');
        $registration->parent_email = \Input::get('parent_email');
        $registration->parent_phone = \Input::get('parent_phone');
        $registration->parent_secondary_phone = \Input::get('parent_secondary_phone');
    }

    $registration->request_loaner = \Input::get('request_loaner') ? true : false;
    $registration->save();
    return json_encode(ModelContracts\Registration::Model($registration, $this->permissions));
  }

  public function postDevices() {
    $this->requirePermission(['internal']);

    $registration = \Route::input('registration');
    $service = strtolower(trim(\Input::get('service')));
    $token = \Input::get('device_token');

    $allowed_services = [
      "messenger",
      "sms",
      "app",
      "app_ios_dev",
      "app_ios_prod"
    ];

    if(!in_array($service, $allowed_services)) {
      return json_encode([
        'ok' => false,
        'error' => 'service must be one of (messenger, sms, app, app_ios_[dev/prod])'
      ]);
    }

    $device = new Models\Batch\Event\Registration\Device;
    $device->batches_events_registration_id = $registration->id;
    $device->service = $service;
    $device->token = $token;
    $device->save();

    return json_encode([
      'ok' => true
    ]);
  }

  public function getSign()
  {
    $this->requirePermission(['internal']);
    $registration = \Route::input('registration');
    Services\Waiver::sync($registration);
    return json_encode(['url' => $registration->waiver->signers[0]->getLink()]);
  }

  public function getSyncWaiver()
  {
    $this->requirePermission(['internal']);
    $registration = \Route::input('registration');
    Services\Waiver::sync($registration);
  }
}
