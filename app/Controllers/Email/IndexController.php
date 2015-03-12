<?php
namespace CodeDay\Clear\Controllers\Email;

use \CodeDay\Clear\Models;

class IndexController extends \Controller {

    public function getParent(){
        $registration = Models\Batch\Event\Registration::where('id', '=', \Input::get('r'))->firstOrFail();

        return \View::make('email-pages/parent', [
            'registration' => $registration
        ]);
    }

    public function postParent(){
        $registration = Models\Batch\Event\Registration::where('id', '=', \Input::get('r'))->firstOrFail();

        $registration->parent_name = \Input::get('parent_name');
        $registration->parent_email = \Input::get('parent_email');
        $registration->parent_phone = \Input::get('parent_phone');
        $registration->parent_secondary_phone = \Input::get('parent_secondary_phone');
        $registration->save();

        return \View::make('email-pages/parent', [
            'registration' => $registration,
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
