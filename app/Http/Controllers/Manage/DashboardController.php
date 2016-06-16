<?php
namespace CodeDay\Clear\Http\Controllers\Manage;

use \CodeDay\Clear\Services;
use \CodeDay\Clear\Models;
use \Carbon\Carbon;

class DashboardController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        $recentRegistrations = [];
        if (count(Models\User::me()->current_managed_events) > 0) {
            $myEvents = implode(',', array_map(function($a) { return "'".$a->id."'"; }, iterator_to_array(Models\User::me()->current_managed_events)));
            $recentRegistrations = Models\Batch\Event\Registration
                ::whereRaw(\DB::raw('batches_event_id IN ('.$myEvents.')'))
                ->where('created_at', '>', Carbon::now()->addWeeks(-1))
                ->limit(10)->get();
        }

        $leaderboard = iterator_to_array(Models\Batch\Event
            ::where('batch_id', '=', Models\Batch::Managed()->id)
            ->get()
        );
        usort($leaderboard, function($a, $b) {
            return count($b->registrations_this_week) - count($a->registrations_this_week);
        });


        return \View::make('dashboard', ['recent_registrations' => $recentRegistrations, 'leaderboard' => $leaderboard]);
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

    public function postNew()
    {
        $event = Models\Batch\Event::where('id', '=', \Input::get('batches_event_id'))->firstOrFail();

        // Check if the user has permission
        if (isset($event) && Models\User::me()->username != $event->manager_username
            && Models\User::me()->username != $event->evangelist_username
            && !$event->isUserAllowed(Models\User::me())
            && !Models\User::me()->is_admin
        ) {
            \App::abort(404);
        }

        if (isset($event) && !$event->batch_id == Models\Batch::Managed()->id) {
            \App::abort(404);
        }

        // Check if the registrant is banned
        $ban = Models\Ban::GetBannedReasonOrNull(\Input::get('email'));
        if ($ban) {
            \Session::flash('error', 'Participant is banned: '.$ban->reason_name.' Violation. Do not admit; have'
                .' participant call (425) 780-7901 to resolve.');
            return \Redirect::back();
        }

        $reg = Services\Registration::CreateRegistrationRecord(
            $event,
            \Input::get('first_name'),
            \Input::get('last_name'),
            \Input::get('email'),
            \Input::get('type')
        );

        if (\Input::get('amount') > 0) {
            try {
                Services\Registration::ChargeCardForRegistrations(
                    [$reg], intval(\Input::get('amount')), \Input::get('token')
                );
            } catch (\Exception $ex) {
                $reg->delete();
                \Session::flash('error', $ex->getMessage());
                return \Redirect::back();
            }
        }

        if (\Input::get('parent_email')) {
            $reg->parent_name = \Input::get('parent_name') ? \Input::get('parent_name') : null;
            $reg->parent_email = \Input::get('parent_email') ? \Input::get('parent_email') : null;
            $reg->parent_phone = \Input::get('parent_phone') ? \Input::get('parent_phone') : null;
            $reg->parent_secondary_phone = \Input::get('parent_secondary_phone') ? \Input::get('parent_secondary_phone') : null;
        } elseif(\Input::get('type') !== 'student') {
            $reg->parent_no_info = true;
        }
        $reg->save();

        if (Carbon::createFromTimestamp(getDayOfEvent()->starts_at)->addHours(-12)->isPast()) {
            $reg->checked_in_at = \Carbon\Carbon::now();
            $reg->save();
            \Event::fire('registration.checkin', \DB::table('batches_events_registrations')->where('id', '=', $reg->id)->get()[0]);
        }

        \Session::flash('status_message', 'Successfully registered.');
        return \Redirect::back();
    }

}
