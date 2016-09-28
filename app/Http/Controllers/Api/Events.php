<?php
namespace CodeDay\Clear\Http\Controllers\Api;

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

    public function getVolunteeredFor()
    {
        $this->requirePermission(['internal']);
        $grants = Models\User\Grant::where('username', '=', \Input::get('username'))->get();

        $response = [];

        foreach($grants as $grant){
          array_push($response, ModelContracts\Grant::Model($grant, $this->permissions));
        }

        return json_encode($response);
    }

    public function getRegistrations()
    {
      $this->requirePermission(['admin']);
      $event = \Route::input('event');
      return json_encode(ModelContracts\Registration::Collection($event->registrationsSortedBy("first_name", "asc"), $this->permissions));
    }

    public function postRegistrations()
    {
      $this->requirePermission(['admin']);
      $event = \Route::input('event');

      $registration = Services\Registration::CreateRegistrationRecord(
          $event,
          \Input::get('first_name'), \Input::get('last_name'),
          \Input::get('email'), "student");

      if ($registration->type !== 'student') {
          $registration->parent_no_info = true;
      }
      $registration->save();

      return json_encode(ModelContracts\Registration::Model($registration, $this->permissions));
    }

    public function getManagedBy()
    {
        $this->requirePermission(['internal']);
        $user = Models\User::where('username', '=', \Route::input('username'))->get();
        return json_encode($user);
    }
}
