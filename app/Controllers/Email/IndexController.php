<?php
namespace CodeDay\Clear\Controllers\Email;

use \CodeDay\Clear\Models;

class IndexController extends \Controller {

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
