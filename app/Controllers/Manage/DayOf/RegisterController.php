<?php
namespace CodeDay\Clear\Controllers\Manage\DayOf;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class RegisterController extends \Controller {

    public function getIndex()
    {
        return \View::make('dayof/register', ['stripe_pk' => \Config::get('stripe.public')]);
    }

    public function postIndex()
    {
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

        $reg->checked_in_at = \Carbon\Carbon::now();
        if (\Input::get('parent_email')) {
            $reg->parent_name = \Input::get('parent_name') ? \Input::get('parent_name') : null;
            $reg->parent_email = \Input::get('parent_email') ? \Input::get('parent_email') : null;
            $reg->parent_phone = \Input::get('parent_phone') ? \Input::get('parent_phone') : null;
            $reg->parent_secondary_phone = \Input::get('parent_secondary_phone') ? \Input::get('parent_secondary_phone') : null;
        } else {
            $reg->parent_no_info = true;
        }
        $reg->save();
        \Event::fire('registration.checkin', \DB::table('batches_events_registrations')->where('id', '=', $reg->id)->get()[0]);

        \Session::flash('status', 'Successfully registered.');
        return \Redirect::to('/dayof/register');
    }
}
