<?php

use \CodeDay\Clear\Services;

\Event::listen('registration.register', function($reg)
{
    Services\Slack::Message('<https://clear.codeday.org/event/'
        .$reg->event->id.'/registrations/attendee/'.$reg->id.'|'.$reg->name.'>'
        .' registered for CodeDay '.$reg->event->name);
});