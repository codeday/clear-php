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
        } else {
            $event->allow_registrations = false;
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