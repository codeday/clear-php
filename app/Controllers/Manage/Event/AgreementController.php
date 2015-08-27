<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class AgreementController extends \Controller {
    public function getIndex()
    {
        $event = \Route::input('event');

        if ($event->agreement_signed_url) {
            return \Redirect::to($event->agreement_signed_url);
        }

        $event->signing_request;
        return \View::make('event/agreement');
    }

    public function postResend()
    {
        $event = \Route::input('event');

        $event->signing_request->SendReminder();

        \Session::flash('status_message', 'Reminder email sent.');
        return \Redirect::to('/event/'.$event->id.'/agreement');
    }

    public function postCheck()
    {
        $event = \Route::input('event');

        if ($event->signing_request->HasPdf()) {
            $event->agreement_pdf;
            \Session::flash('status_message', 'Welcome to the team!');
            return \Redirect::to('/event/'.$event->id);
        } else {
            \Session::flash('error', 'Agreement is not signed.');
            return \Redirect::to('/event/'.$event->id.'/agreement');
        }

    }
}