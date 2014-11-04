<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class EmailsController extends \Controller {
    public function getIndex()
    {
        return \View::make('event/emails', ['email_templates' => Models\EmailTemplate::all()]);
    }

    public function postSend()
    {
        $event = \Route::input('event');
        $to = \Input::get('to');
        $from = \Input::get('from');
        $subject = \Input::get('subject');
        $message = \Input::get('message');

        $to_raw = $this->getToList($to);
        $from_raw = $this->getFrom($from);

        // Check if we want to generate a tracking promo code
        $tracking_promo_code = null;
        if (str_contains($message, 'tracking_promo_code')) {
            $promotion = new Models\Batch\Event\Promotion;
            $promotion->batches_event_id = $event->id;
            $promotion->code = strtoupper(str_random(5));
            $promotion->notes = 'Tracking code for email "'.$subject.'", sent '.date('F j').'.';
            $promotion->percent_discount = 20;
            $promotion->expires_at = null;
            $promotion->allowed_uses = null;
            $promotion->save();

            $tracking_promo_code = $promotion->code;
        }

        $loader = new \Twig_Loader_String();
        $twig = new \Twig_Environment($loader);
        foreach ($to_raw as $recipient) {
            $context = [
                'first_name' => $recipient->first_name,
                'last_name' => $recipient->last_name,
                'event' => $event,
                'me' => Models\User::me(),
                'tracking_promo_code' => $tracking_promo_code,
                'link' => 'https://codeday.org/'.$event->region_id,
                'register_link' => 'https://codeday.org/'.$event->region_id.'/register'
            ];

            $message_rendered = $twig->render($message, $context);
            $message_rendered = $twig->render('{{ content|nl2br }}', ['content' => $message_rendered]);
            $subject_rendered = $twig->render($subject, $context);

            try {
                \Mail::queue('emails/blank', ['content' => $message_rendered], function($envelope) use ($recipient, $subject_rendered, $from_raw) {
                    $envelope->from($from_raw->email, $from_raw->name);
                    $envelope->to(trim($recipient->email), $recipient->name);
                    $envelope->subject($subject_rendered);
                });
            } catch (\Exception $ex) {}
        }

        return \Redirect::to('/event/'.$event->id.'/emails');
    }

    private function getFrom($from)
    {
        $event = \Route::input('event');

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
            case "studentrnd":
                return (object)[
                    'name' => 'CodeDay '.$event->name,
                    'email' => $event->webname.'@codeday.org'
                ];
            default:
                \App::abort(403);
        }
    }

    private function getToList($to)
    {
        $event = \Route::input('event');

        switch ($to) {
            case "me":
                return [
                    (object)[
                        'name' => Models\User::me()->name,
                        'first_name' => Models\User::me()->first_name,
                        'last_name' => Models\User::me()->last_name,
                        'email' => Models\User::me()->username.'@studentrnd.org'
                    ]
                ];
            case "attendees":
                return array_map(function($user){
                    return (object)[
                        'name' => $user->name,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email
                    ];
                }, iterator_to_array($event->registrations));
            case "notify":
                return array_map(function($user){
                    return (object)[
                        'name' => $user->email,
                        'first_name' => null,
                        'last_name' => null,
                        'email' => $user->email
                    ];
                }, iterator_to_array($event->notify));
            case "notify-unreg":
                return array_map(function($user){
                    return (object)[
                        'name' => $user->email,
                        'first_name' => null,
                        'last_name' => null,
                        'email' => $user->email
                    ];
                }, $event->unregistered_notify);
            default:
                \App::abort(403);
        }
    }
}