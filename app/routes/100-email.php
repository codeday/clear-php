<?php

\Route::group(['namespace' => 'Email', 'prefix' => 'e'], function() {
    \Route::controller('/incoming', 'IncomingController');
    \Route::controller('', 'IndexController');
});