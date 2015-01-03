<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

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
            \Session::flash('status_message', 'Event '.($event->allow_registrations ? 'enabled' : 'disabled'));
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
        $event->save();

        return \Redirect::to('/event/'.$event->id);
    }

    public function getChartdata()
    {
        $event = \Route::input('event');
        $first_registration = Models\Batch\Event\Registration::where('batches_event_id', '=', $event->id)
            ->orderBy('created_at', 'ASC')
            ->first();

        if (!$first_registration) {
            return '';
        }

        $data = ['date,delta,registrations'];
        for ($date = $first_registration->created_at->copy()->subDay();
             $date->isPast();
             $date->addDay()) {
            $data[] = implode(',', [
                $date->format('j-M-y'),
                count($event->getRegistrationsOn($date)),
                count($event->getRegistrationsAsOf($date->copy()->addDay())) // at end of day
            ]);
        }

        return implode("\n", $data);
    }
} 