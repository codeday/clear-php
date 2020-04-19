<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;
use \CodeDay\Clear\Services;

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

    public function getHasAccess()
    {
        $user = Models\User::where('username', '=', \Input::get('username'))->firstOrFail();

        $events = Models\Batch\Event
            ::select('batches_events.*')
            ->leftJoin('users_grants', 'users_grants.batches_event_id', '=', 'batches_events.id')
            ->where('manager_username', '=', $user->username)
            ->orWhere('coach_username', '=', $user->username)
            ->orWhere('evangelist_username', '=', $user->username)
            ->orWhere('users_grants.username', '=', $user->username)
            ->get();

        return ModelContracts\Event::Collection($events, $this->permissions);
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
            \Input::get('email'),
            // default = student
            \Input::get('type', 'student')
        );

        // Handle PaymentIntents
        if(\Input::get('stripe_pi', '') !== "") {
            \Stripe\Stripe::setApiKey(\Config::get('stripe.secret'));
            $pi = \Stripe\PaymentIntent::retrieve(\Input::get('stripe_pi'));

            $registration->stripe_id = $pi->charges->data[0]->id;
            $registration->amount_paid = $pi->amount_received / 100.0;
            $registration->is_earlybird_pricing = $event->is_earlybird_pricing;
        }

        $registration->age = \Input::get('age', null);
        $registration->parent_name = \Input::get('parent_name', null);
        $registration->parent_email = \Input::get('parent_email', null);
        $registration->parent_phone = \Input::get('parent_phone', null);
        $registration->parent_secondary_phone = \Input::get('parent_secondary_phone', null);

        $registration->save();

        return json_encode(ModelContracts\Registration::Model($registration, $this->permissions));
    }

    public function getAnnouncements()
    {
        $event = \Route::input('event');
        return json_encode(ModelContracts\Announcement::Collection($event->announcements, $this->permissions));
    }

    public function getManagedBy()
    {
        $this->requirePermission(['internal']);
        $user = Models\User::where('username', '=', \Route::input('username'))->get();
        return json_encode($user);
    }
}
