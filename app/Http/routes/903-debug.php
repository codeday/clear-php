<?php

\Route::group(['namespace' => 'Manage\Debug', 'prefix' => 'debug', 'before' => 's5_admin', 'middleware' => ['web']], function() {
    \Route::get('', function(){ return \Redirect::to('/debug/log'); });
    \Route::controller('/log', 'LogController');
    \Route::controller('/queue', 'QueueController');
});
