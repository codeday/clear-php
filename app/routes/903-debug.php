<?php

\Route::when('debug/*', 'csrf', ['post']);
\Route::group(['namespace' => 'Manage\Debug', 'prefix' => 'debug', 'before' => 's5_admin'], function() {
    \Route::get('', function(){ return \Redirect::to('/debug/decrypt'); });
    \Route::controller('/log', 'LogController');
    \Route::controller('/queue', 'QueueController');
    \Route::controller('/decrypt', 'DecryptController');
});