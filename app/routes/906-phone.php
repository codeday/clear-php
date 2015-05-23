<?php

\Route::filter('twilio_valid', function() {
    if (\Input::get('AccountSid') !== \Config::get('twilio.sid')) {
        \App::abort(401);
    }
});

\Route::group(['namespace' => 'Phone', 'prefix' => 'phone', 'before' => 'twilio_valid'], function() {
    \Route::controller('/support', 'IncomingSupportController');
});
