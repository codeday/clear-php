<?php
namespace CodeDay\Clear\Http\Controllers\Email;

use \CodeDay\Clear\Models;
use CodeDay\Clear\Services;

class IndexController extends \CodeDay\Clear\Http\Controller {
    public function getFeedback(){
        $registration = Models\Batch\Event\Registration::where('id', '=', \Input::get('r'))->firstOrFail();

        return \View::make('email-pages/feedback', [
            'registration' => $registration
        ]);
    }

    public function getParent(){
        $registration = Models\Batch\Event\Registration::where('id', '=', \Input::get('r'))->firstOrFail();

        if (($registration->parent_email || $registration->parent_no_info) && !isset($registration->waiver_signing_id)) {
            Services\Waiver::send($registration);
        }

        return \View::make('email-pages/parent', [
            'registration' => $registration
        ]);
    }

    public function postParent(){
        $registration = Models\Batch\Event\Registration::where('id', '=', \Input::get('r'))->firstOrFail();

        $registration->parent_name = \Input::get('parent_name') ? \Input::get('parent_name') : null;
        $registration->parent_email = \Input::get('parent_email') ? \Input::get('parent_email') : null;
        $registration->parent_phone = $this->sanitizePhone(\Input::get('parent_phone'));
        $registration->parent_secondary_phone = $this->sanitizePhone(\Input::get('parent_secondary_phone'));
        $registration->age = \Input::get('attendee_age') ? \Input::get('attendee_age') : null;
        $registration->parent_no_info = $registration->age >= 18; 

        $registration->save();

        try {
            list($fname, $lname) = preg_split('/\s+/', $registration->parent_name, 2);
            (new \Customerio\Api(\config('customerio.site'), \config('customerio.secret'), new \Customerio\Request))
                ->createCustomer($registration->parent_email, $registration->parent_email, [
                    'type' => 'parent',
                    'first_name' => $fname,
                    'last_name' => $lname,
                    'city' => $registration->event->name,
                    'season' => $registration->event->batch->name,
                    'student_first_name' => $registration->first_name,
                    'student_last_name' => $registration->last_name,
                ]);
        } catch (\Exception $ex) {}



        if ($registration->parent_email || $registration->parent_no_info) {
            Services\Waiver::send($registration);
        }

        return \View::make('email-pages/parent', [
            'registration' => $registration,
            'success' => true
        ]);
    }

    private function sanitizePhone($phone)
    {
        $stripped = preg_replace('/\D/', '', $phone);
        if (strlen($stripped) < 11) {
            $stripped = '1'.$stripped;
        }

        if (strlen($stripped) === 11) {
            return $stripped;
        } else {
            return null;
        }
    }

    public function getSmsunsubscribe()
    {
        return \View::make('email-pages/sms_unsubscribe', ['me' => Models\User::me()]);
    }

    public function postSmsunsubscribe()
    {
        $user = Models\User::me();
        $user->sms_optout =\Input::get('sms_optout') ? true : false;
        $user->save();

        return \View::make('email-pages/sms_unsubscribe', [
            'me' => Models\User::me(),
            'success' => true
        ]);
    }

    public function getUnsubscribe()
    {
        $email = \Input::get('e');
        $unsub = Models\Unsubscribe::where('email', '=', $email)->first();

        return \View::make('email-pages/unsubscribe', [
            'type' => $unsub ? $unsub->type : 'none'
        ]);
    }

    public function postUnsubscribe()
    {
        $email = \Input::get('e');
        $type = \Input::get('type');

        $unsub = Models\Unsubscribe::where('email', '=', $email)->first();

        if ($type !== 'none') {
            if (!$unsub) {
                $unsub = new Models\Unsubscribe;
                $unsub->email = $email;
            }

            $unsub->type = $type;
            $unsub->save();
        }else if ($type === 'none' && $unsub) {
            $unsub->delete();
            $unsub = null;
        }


        return \View::make('email-pages/unsubscribe', [
            'type' => $unsub ? $unsub->type : 'none',
            'success' => true
        ]);
    }
}
