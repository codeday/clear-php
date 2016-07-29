<?php

\Route::group(['namespace' => 'Api', 'prefix' => 'api', 'middleware' => ['api']], function() {
    \Route::get('stats', 'Stats@getIndex');

    \Route::get('application/', 'Application@getApplication');

    \Route::controller('register/{event}', 'Register');

    \Route::controller('regions', 'Regions');
    \Route::controller('checkin', 'Checkin');
    \Route::get('region/{region}', 'Regions@getRegion');

    \Route::controller('events', 'Events');
    // TODO normalize "-" and "_", just need to make sure other things don't get confused.
    \Route::get('event/{event}', 'Events@getEvent');
    \Route::get('events/managed-by/{username}', 'Events@getManagedBy');
    \Route::get('events/volunteered-for', 'Events@getVolunteeredFor');

    \Route::get('registration/by-email/{email}', 'Registrations@getByEmail');
    \Route::get('registration/{registration}', 'Registrations@getRegistration');

    \Route::controller('promotions', 'Promotions');
    \Route::post('promotions/new', 'PromotionsController@postNew');
    \Route::post('promotions/delete', 'PromotionsController@postDelete');
    \Route::get('promotion/{promotion}', 'Promotions@getPromotion');

    \Route::get('/i/{class}/{id}_{imagesize}.jpg', 'ImageController@redirectPhoto');
    \Route::get('/i/{class}/{id}_{imagesize}/{timestamp}.jpg', 'ImageController@showPhoto');

    \Route::controller('notify', 'Notify');
    \Route::controller('batches', 'Batches');
});
