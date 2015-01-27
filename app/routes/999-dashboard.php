<?php

\Route::group(['namespace' => 'Manage', 'before' => 's5_manage_events'], function() {
    \Route::get('/', 'DashboardController@getIndex');
    \Route::get('/change-batch', 'DashboardController@getChangeBatch');
    \Route::get('/updates.json', 'DashboardController@getUpdates');
    \Route::get('/logout', function(){
        \CodeDay\Clear\Models\User::me()->forget();
        return \Redirect::to('https://s5.studentrnd.org/login/logout');
    });
});