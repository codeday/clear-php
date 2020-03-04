<?php
\Route::group(['namespace' => 'Manage', 'before' => 's5_manage_events', 'middleware' => ['web']], function() {
    \Route::get('/', 'DashboardController@getIndex');
    \Route::get('/help', 'DashboardController@getHelp');
    \Route::get('/updates.json', 'DashboardController@getUpdates');
    \Route::post('/new', 'DashboardController@postNew');
    \Route::controller('/search', 'SearchController');
});

\Route::group(['namespace' => 'Manage', 'before' => 's5_manage_events'], function() {
    \Route::controller('/front', 'FrontController');
});
