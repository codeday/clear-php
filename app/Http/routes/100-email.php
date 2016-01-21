<?php

\Route::group(['namespace' => 'Email', 'prefix' => 'e', 'middleware' => ['web']], function() {
    \Route::controller('/incoming', 'IncomingController');
    \Route::controller('/flyer/{event}', 'FlyerController');
    \Route::controller('/flyer', 'FlyerController');
    \Route::controller('', 'IndexController');
});