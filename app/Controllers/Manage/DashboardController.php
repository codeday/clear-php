<?php
namespace CodeDay\Clear\Controllers\Manage;

use \CodeDay\Clear\Models;

class DashboardController extends \Controller {

    public function getIndex()
    {
        return \View::make('dashboard');
    }

    public function getUpdates()
    {
        if (Models\User::me()->is_admin) {
            $events = Models\Batch::Managed()->events;
        } else {
            $events = Models\User::me()->current_managed_events;
        }

        $event_update = [];
        foreach ($events as $event) {
            $event_update[$event->id] = [
                'registrations' => $event->registrations->count(),
                'here' => count($event->attendees_here),
                'today' => count($event->registrations_today),
                'this_week' => count($event->registrations_this_week),
                'percent' => $event->registration_estimate ? round(($event->registrations->count()/$event->registration_estimate)*100) : 0,
                'predicted' => $event->pretty_prediction(),
                'notify' => $event->notify->count(),
                'allow_registrations' => $event->allow_registrations
            ];
        }

        return json_encode($event_update);
    }
}
