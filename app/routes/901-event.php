<?php

\Route::group(['namespace' => 'Manage\Event', 'prefix' => 'event/{event}', 'before' => 's5_manage_event'], function() {
    \Route::controller('/venue', 'VenueController');
    \Route::controller('/promotions', 'PromotionsController');
    \Route::controller('/emails', 'EmailsController');
    \Route::controller('/subusers', 'SubusersController');

    \Route::get('/registrations/attendee/{registration}', 'RegistrationsController@getAttendee');
    \Route::post('/registrations/attendee/{registration}', 'RegistrationsController@postAttendee');
    \Route::post('/registrations/attendee/{registration}/cancel', 'RegistrationsController@postCancel');
    \Route::post('/registrations/attendee/{registration}/refund', 'RegistrationsController@postRefund');
    \Route::controller('/registrations', 'RegistrationsController');

    \Route::get('/', 'IndexController@getIndex');
    \Route::get('/chartdata.csv', 'IndexController@getChartdata');
    \Route::post('/update-registration-status', 'IndexController@postUpdateRegistrationStatus');
    \Route::post('/notes', 'IndexController@postNotes');
});