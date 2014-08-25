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
        if (Models\User::me()->is_admin && count(Models\User::me()->managedEvents()) == 0) {
            $events = Models\Batch::Loaded()->events;
        } else {
            $events = Models\User::me()->managed_events;
        }

        $event_update = [];
        foreach ($events as $event) {
            $event_update[$event->id] = [
                'registrations' => $event->registrations->count(),
                'today' => count($event->registrations_today),
                'this_week' => count($event->registrations_this_week),
                'percent' => round(($event->registrations->count()/$event->registration_estimate)*100),
                'predicted' => '?'
            ];
        }

        return json_encode($event_update);
    }
} 