<?php
namespace CodeDay\Clear\Controllers\Manage\DayOf;

use \Carbon\Carbon;
use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class RegisterController extends \Controller {

    public function getIndex()
    {
        return \View::make('dayof/register', ['stripe_pk' => \Config::get('stripe.public')]);
    }

    public function postIndex()
    {
        // Check if the registrant is banned
        $ban = Models\Ban::GetBannedReasonOrNull(\Input::get('email'));
        if ($ban) {
            \Session::flash('error', 'Participant is banned: '.$ban->reason_name.' Violation. Do not admit; have'
                .' participant call (425) 780-7901 to resolve.');
            return \Redirect::to('/dayof/register');
        }

        $reg = Services\Registration::CreateRegistrationRecord(
            getDayOfEvent(),
            \Input::get('first_name'),
            \Input::get('last_name'),
            \Input::get('email')
        );

        try {
            Services\Registration::SendTicketEmail($reg);
        } catch (\Exception $ex) {}

        if (\Input::get('amount') > 0) {
            try {
                Services\Registration::ChargeCardForRegistrations(
                    [$reg], intval(\Input::get('amount')), \Input::get('token')
                );
            } catch (\Exception $ex) {
                \Session::flash('error', $ex->getMessage());
                return \Redirect::to('/dayof/register');
            }
        }

        if (\Input::get('parent_email')) {
            $reg->parent_name = \Input::get('parent_name') ? \Input::get('parent_name') : null;
            $reg->parent_email = \Input::get('parent_email') ? \Input::get('parent_email') : null;
            $reg->parent_phone = \Input::get('parent_phone') ? \Input::get('parent_phone') : null;
            $reg->parent_secondary_phone = \Input::get('parent_secondary_phone') ? \Input::get('parent_secondary_phone') : null;
        } else {
            $reg->parent_no_info = true;
        }
        $reg->save();

        if (Carbon::createFromTimestamp(getDayOfEvent()->starts_at)->addHours(-12)->isPast()) {
            $reg->checked_in_at = \Carbon\Carbon::now();
            $reg->save();
            \Event::fire('registration.checkin', \DB::table('batches_events_registrations')->where('id', '=', $reg->id)->get()[0]);
        } else {
            Services\Registration::SendTicketEmail($reg);
            Services\Registration::EnqueueSurveyEmail($reg);
        }

        \Session::flash('status_message', 'Successfully registered.');
        return \Redirect::to('/dayof/register');
    }
}
