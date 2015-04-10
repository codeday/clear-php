<?php

\Route::group(['namespace' => 'Manage\Docs', 'prefix' => 'docs', 'before' => 's5_manage_events'], function() {
    \Route::get('/model/{model}', 'ModelController@getIndex');
});