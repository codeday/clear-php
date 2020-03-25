<?php

use \CodeDay\Clear\Services;

\Event::listen('announcement.*', function($data){
  \CodeDay\Clear\Models\Application\Webhook::Fire(\Event::firing(), $data);
});