<?php
\Route::group(['namespace' => 'Manage', 'before' => 's5_manage_events', 'middleware' => ['web']], function() {
    \Route::get('/', 'DashboardController@getIndex');
    \Route::get('/updates.json', 'DashboardController@getUpdates');
    \Route::post('/new', 'DashboardController@postNew');
    \Route::get('/logout', function(){
        \CodeDay\Clear\Models\User::me()->forget();
        return \Redirect::to('https://s5.studentrnd.org/login/logout');
    });
    \Route::controller('/search', 'SearchController');
});

\Route::group(['namespace' => 'Manage', 'before' => 's5_manage_events'], function() {
    \Route::get('/front-plugin', 'DashboardController@getFrontPlugin');
    \Route::get('/front-plugin-data', 'DashboardController@getFrontPluginData');
});
