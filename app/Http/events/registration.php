<?php

use \CodeDay\Clear\Services;

\Event::listen('registration.register', function($reg){
});

\Event::listen('registration.*', function($data){
  \CodeDay\Clear\Models\Application\Webhook::Fire(\Event::firing(), $data);
});
