<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;
use \CodeDay\Clear\ModelContracts;

class EmailsController extends \Controller {

    public function getIndex()
    {
        return \View::make('tools/emails');
    }

    public function postSend()
    {
        $to = \Input::get('to');
        $from = \Input::get('from');
        $subject = \Input::get('subject');
        $message = \Input::get('message');
        $events = \Input::get('events');
        $tracking_promo_code = strtoupper(str_random(5));

        foreach ($events as $event_id) {
            $event = Models\Batch\Event::where('id', '=', $event_id)->first();
            if (!$event) {
                continue;
            }

            $from_raw = $this->getFrom($from, $event);

            // Check if we want to generate a tracking promo code
            if (str_contains($message, 'tracking_promo_code')) {
                $promotion = new Models\Batch\Event\Promotion;
                $promotion->batches_event_id = $event->id;
                $promotion->code = $tracking_promo_code;
                $promotion->notes = 'Tracking code for email "'.$subject.'", sent '.date('F j').'.';
                $promotion->percent_discount = 20;
                $promotion->expires_at = null;
                $promotion->allowed_uses = null;
                $promotion->save();
            }

            Services\Email::SendToEvent(
                $from_raw->name, $from_raw->email,
                $event, $to,
                $subject,
                $message,
                null,
                [
                    'me' => Models\User::me(),
                    'event' => ModelContracts\Event::Model($event),
                    'tracking_promo_code' => $tracking_promo_code,
                    'link' => 'https://codeday.org/'.$event->region_id,
                    'register_link' => 'https://codeday.org/'.$event->region_id.'/register'
                ]
            );

            $email_sent = new Models\EmailSent;
            $email_sent->to = $to;
            $email_sent->from = $from;
            $email_sent->subject = $subject;
            $email_sent->message = $message;
            $email_sent->batches_event_id = $event->id;
            $email_sent->save();
        }

        \Session::flash('status_message', 'Email enqueued');

        return \Redirect::to('/tools/emails');
    }

    private function getFrom($from, $event)
    {
        switch ($from) {
            case "me":
                return (object)[
                    'name' => Models\User::me()->name,
                    'email' => Models\User::me()->username.'@studentrnd.org'
                ];
            case "manager":
                return (object)[
                    'name' => $event->manager->name,
                    'email' => $event->manager->username.'@studentrnd.org'
                ];
            case "codeday":
                return (object)[
                    'name' => 'CodeDay '.$event->name,
                    'email' => $event->webname.'@codeday.org'
                ];
            default:
                \App::abort(403);
        }
    }
}