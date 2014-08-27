<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

class IndexController extends \Controller {
    public function getIndex()
    {
        return \View::make('event/index');
    }

    public function postUpdateRegistrationStatus()
    {
        $event = \Route::input('event');

        if ($event->venue) {
            $event->allow_registrations = \Input::get('allow_registrations');
        } else {
            $event->allow_registrations = false;
        }

        $event->save();
        return \Redirect::to('/event/'.$event->id);
    }
} 