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
        return \Redirect::to('https://codeday.vip/'.\Input::get('r'));
    }

    public function postParent(){
        return \Redirect::to('https://codeday.vip/'.\Input::get('r'));
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
}
