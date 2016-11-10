<?php

\Route::group(['namespace' => 'Manage\Docs', 'prefix' => 'docs', 'before' => 's5_manage_events', 'middleware' => ['web']], function() {
    \Route::get('/model/{model}', 'ModelController@getIndex');
});