<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;
use CodeDay\Clear\Services;

class SubusersController extends \CodeDay\Clear\Http\Controller {
    public function getIndex()
    {
        return \View::make('event/subusers');
    }

    public function postRevoke()
    {
        $event = \Route::input('event');

        Models\User\Grant::where('batches_event_id', '=', $event->id)
            ->where('username', '=', \Input::get('username'))
            ->firstOrFail()
            ->delete();

        \Session::flash('status_message', \Input::get('username').' removed');

        return \Redirect::to('/event/'.$event->id.'/subusers');
    }

    public function postGrant()
    {
        $event = \Route::input('event');
        $user = Models\User::fromS5Username(\Input::get('username'));

        if ($user->username == $event->manager_username) {
            \Session::flash('error', 'Cannot add the event manager as a subuser of the event');
            return \Redirect::to('/event/'.$event->id.'/subusers');
        }

        if ($user->username) {
            $grant = new Models\User\Grant;
            $grant->username = \Input::get('username');
            $grant->batches_event_id = $event->id;
            $grant->save();

            Services\Email::SendOnQueue(
                'CodeDay', 'codeday@srnd.org',
                $user->name, $user->username.'@srnd.org',
                'Clear Access Granted', null,
                \View::make('emails/actions/subuser', ['user' => $user, 'event' => $event, 'from' => Models\User::me()]),
                false
            );

            return \Redirect::to('/event/'.$event->id.'/subusers');
        } else {
            \Session::flash('error', 'User not found');
            return \Redirect::to('/event/'.$event->id.'/subusers');
        }
    }
}
