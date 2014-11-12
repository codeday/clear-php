<?php
namespace CodeDay\Clear\Controllers\Manage;

use \CodeDay\Clear\Models;

class DashboardController extends \Controller {

    public function getIndex()
    {
        return \View::make('dashboard');
    }

    public function getChangeBatch()
    {
        if (\Input::get('id')) {
            Models\Batch::find(\Input::get('id'))->manage();
            return \Redirect::to('/');
        } else {
            return \View::make('change_batch');
        }
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
                'today' => count($event->registrations_today),
                'this_week' => count($event->registrations_this_week),
                'percent' => $event->registration_estimate ? round(($event->registrations->count()/$event->registration_estimate)*100) : 0,
                'predicted' => '?',
                'notify' => $event->notify->count(),
                'allow_registrations' => $event->allow_registrations
            ];
        }

        return json_encode($event_update);
    }
} 
