<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class SupportController extends \Controller {
    public function getIndex()
    {
        return \View::make('event/support');
    }

    public function postIndex()
    {
        $event = \Route::input('event');
        $support_destination = \Input::get('support_destination') ? \Input::get('support_destination') : null;

        if ($support_destination) {
            $domainName = substr(strrchr($support_destination, "@"), 1);
            if (in_array($domainName, ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'])) {
                \Session::flash('error', 'Cannot send to personal email.');
                return \Redirect::to('/event/'.$event->id.'/support');
            }
        }

        $event->support_destination = $support_destination;
        $event->save();

        \Session::flash('status_message', 'Support destination updated');

        return \Redirect::to('/event/'.$event->id.'/support');
    }
}