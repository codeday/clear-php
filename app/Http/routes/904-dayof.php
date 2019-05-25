<?php

use \CodeDay\Clear\Models;

\Route::filter('dayof_event', function(){
    getDayOfEvent();
});

\Route::group(['namespace' => 'Manage\DayOf', 'prefix' => 'dayof', 'before' => 's5_manage_events|dayof_event', 'middleware' => ['web']], function() {

    \Route::get('switch', function() {
        $event = Models\Batch\Event::where('id', '=', \Input::get('event'))->firstOrFail();
        if (Models\User::me()->username != $event->manager_username
            && Models\User::me()->username != $event->evangelist_username
            && !$event->isUserAllowed(Models\User::me())
            && !Models\User::me()->is_admin) {
            \App::abort(401);
        }

        \Session::put('dayof_event', $event->id);
        return \Redirect::to(\Request::header('referer') ? \Request::header('referer') : '/dayof');
    });

    \Route::get('', function() { return \Redirect::to('/dayof/checkin'); });


    \Route::controller('/checkin', 'CheckinController');
    \Route::controller('/equipment', 'EquipmentController');
    \Route::controller('/break', 'BreakController');
    \Route::controller('/deck', 'DeckController');
    \Route::controller('/codecup', 'CodeCupController');
    \Route::controller('/emergency', 'EmergencyController');
    \Route::controller('/support-calls', 'SupportCallsController');
});

if (!function_exists('getDayOfEvent')) {
    function getDayOfEvent()
    {
        $event = null;

        if (\Session::has('dayof_event')) {
            $event = Models\Batch\Event::where('id', '=', \Session::get('dayof_event'))->first();
        }

        if (isset($event) && Models\User::me()->username != $event->manager_username
            && Models\User::me()->username != $event->evangelist_username
            && !$event->isUserAllowed(Models\User::me())
            && !Models\User::me()->is_admin
        ) {
            $event = null;
        }

        if (isset($event) && $event->batch_id !== Models\Batch::Managed()->id) {
            $event = null;
        }

        if (!isset($event)) {
            if (Models\User::me()->is_admin) {
                $event = Models\Batch::Managed()->events[0];
            } else {
                $event = Models\User::me()->current_managed_events[0];
            }
            \Session::put('dayof_event', $event->id);
        }

        \View::share('event', $event);
        return $event;
    }
}
