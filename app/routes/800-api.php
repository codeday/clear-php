<?php

\Route::group(['namespace' => 'Api', 'prefix' => 'api'], function() {
    \Route::controller('register/{event}', 'Register');

    \Route::controller('regions', 'Regions');
    \Route::get('region/{region}', 'Regions@getRegion');

    \Route::controller('events', 'Events');
    \Route::get('event/{event}', 'Events@getEvent');


    \Route::controller('notify', 'Notify');
    \Route::controller('batches', 'Batches');
});