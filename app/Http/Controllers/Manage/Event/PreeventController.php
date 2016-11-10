<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class PreeventController extends \CodeDay\Clear\Http\Controller {
    public function __construct()
    {
        if (\Route::input('event')->preevent_email_sent_at !== null) {
            \App::abort(403);
        }
    }

    public function getIndex()
    {
        return \View::make('event/preevent');
    }

    public function postIndex()
    {
        $event = \Route::input('event');
        $event->preevent_additional = \Input::get('preevent_additional') ? \Input::get('preevent_additional') : null;
        $event->save();

        return \Redirect::to('/event/'.$event->id.'/preevent');
    }

    public function getSample()
    {
        $event = \Route::input('event');
        $reg = $event->registrations->first();
        $from = '"CodeDay '.$reg->event->name.'" <'.$reg->event->webname.'@codeday.org>';
        $to = '"'.$reg->name.'" <'.$reg->email.'>';
        $subject = 'CodeDay is Shortly Upon Us';
        $body = \View::make('emails/preevent_text', ['registration' => ModelContracts\Registration::Model($reg, ['internal'])])->render();

        $contents= "To: $to\nFrom: $from\nSubject: $subject\n\n$body";

        $response = \Response::make($contents, 200);
        $response->header('Content-Type', 'text/plain');
        return $response;
    }
} 
