<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class IndexController extends \CodeDay\Clear\Http\Controller {
    public function getIndex()
    {
        $event = \Route::input('event');
        $regNotes = Models\Batch\Event\Registration::where('batches_event_id', '=', $event->id)
                                ->whereNotNull('notes')
                                ->get();
        return \View::make('event/index', ['regNotes' => $regNotes]);
    }

    public function getMyEvent()
    {
      $event = Models\User::me()->current_managed_events->sortBy('date_created')->last();
      return \Redirect::to('/event/' . $event->id . '/' . \Route::input('path').'?'.http_build_query(\Request::query()));
    }

    public function postUpdateRegistrationStatus()
    {
        $event = \Route::input('event');

        if ($event->venue) {
            $event->allow_registrations = \Input::get('allow_registrations');
            \Session::flash('status_message', 'Event '.($event->allow_registrations ? 'enabled' : 'disabled'));
            \Event::fire('registration.'.($event->allow_registrations ? 'open' : 'close'), ModelContracts\Event::Model($event, ['internal', 'admin']));
        } else {
            $event->allow_registrations = false;
            if (\Input::get('allow_registrations')) {
                \Session::flash('error', 'Could not enable event with no venue.');
            }
        }

        $event->save();
        return \Redirect::to('/event/'.$event->id);
    }

    public function postUpdateWaitlistStatus()
    {
        $event = \Route::input('event');

        if ($event->venue) {
            $event->allow_waitlist_signups = \Input::get('allow_waitlist_signups');
            \Session::flash('status_message', 'Waitlist '.($event->allow_waitlist_signups ? 'opened' : 'closed'));
        } else {
            \Session::flash('error', 'Could not save waitlist status for disabled event.');
        }


        $event->save();
        return \Redirect::to('/event/'.$event->id);
    }

    public function postNotes()
    {
        $event = \Route::input('event');

        $event->notes = \Input::get('notes');
        $event->public_notes = \Input::get('public_notes');
        $event->save();

        return \Redirect::to('/event/'.$event->id);
    }

}
