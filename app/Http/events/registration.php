<?php

use \CodeDay\Clear\Services;

\Event::listen('registration.register', function($reg)
{
    Services\Slack::Message('<https://clear.codeday.org/event/'
        .$reg->event->id.'/registrations/attendee/'.$reg->id.'|'.$reg->name.'>'
        .' registered for CodeDay '.$reg->event->name);
});

\Event::listen('registration.*', function($data){
  \CodeDay\Clear\Models\Application\Webhook::Fire(\Event::firing(), $data);
});

\Event::listen('slack.registration.register', function($data){
  \CodeDay\Clear\Models\Application\Webhook::FireSlack(\Event::firing(), $data);
});