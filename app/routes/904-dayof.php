<?php

\Route::when('dayof/*', 'csrf', ['post']);
\Route::group(['namespace' => 'Manage\DayOf', 'prefix' => 'dayof', 'before' => 's5_manage_events'], function() {
    \Route::get('', function() { return \Redirect::to('/dayof/checkin'); });

    \Route::controller('/checkin', 'CheckinController');
    \Route::controller('/deck', 'DeckController');

    \Route::group(['before' => 's5_admin'], function(){
        \Route::controller('send-sms', 'SendSmsController');
    });
});
