<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class IndexController extends \CodeDay\Clear\Http\Controller {
    public function getIndex()
    {
        return \View::make('event/index');
    }

    public function getMyEvent()
    {
      $event = Models\User::me()->current_managed_events->sortBy('date_created')->last();
      return \Redirect::to('/event/' . $event->id . '/' . \Route::input('path'));
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

    public function getAgedata()
    {
        $event = \Route::input('event');

        $ageGroups = [0, 10, 14, 19, 22];
        $ageLabels = ["Elementary", "Middle", "High", "College", "Post-College"];
        $ageBins = [0, 0, 0, 0, 0];
        $registrations = Models\Batch\Event\Registration
            ::where('batches_event_id', '=', $event->id)
            ->where('type', '=', 'student')
            ->orderBy('created_at', 'ASC')
            ->get();
        foreach ($registrations as $registration) {
            $age = $registration->age;
            for ($i = 0; $i < count($ageGroups); $i++) {
                if ($age >= $ageGroups[$i] && (!isset($ageGroups[$i+1]) || $age < $ageGroups[$i+1])) {
                    $ageBins[$i]++;
                    break;
                }
            }
        }

        $result = array_map(null, $ageLabels, $ageBins);
        return "age,count\n".implode("\n", array_map(function($bin) { return implode(",", $bin); }, $result));

    }

    public function getChartdata()
    {
        $event = \Route::input('event');
        $first_registration = Models\Batch\Event\Registration
            ::where('batches_event_id', '=', $event->id)
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
