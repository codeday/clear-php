<?php

\Route::group(['namespace' => 'Manage\Debug', 'prefix' => 'debug', 'before' => 's5_admin'], function() {
    \Route::when('*', 'csrf', ['post']);
    \Route::get('', function(){ return \Redirect::to('/debug/log'); });
    \Route::controller('/log', 'LogController');
    \Route::controller('/queue', 'QueueController');
});