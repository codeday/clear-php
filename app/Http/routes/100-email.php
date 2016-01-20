<?php

\Route::group(['namespace' => 'Email', 'prefix' => 'e', 'middleware' => ['web']], function() {
    \Route::controller('/incoming', 'IncomingController');
    \Route::controller('', 'IndexController');
});