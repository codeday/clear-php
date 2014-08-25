<?php

\Route::group(['namespace' => 'Manage', 'before' => 's5_user'], function() {
    \Route::get('/', 'DashboardController@getIndex');
    \Route::get('/updates.json', 'DashboardController@getUpdates');
});