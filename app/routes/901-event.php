<?php

\Route::get('event/{event}/registrations/csv', 'Manage\Event\RegistrationsController@getCsv');

\Route::group(['namespace' => 'Manage\Event', 'prefix' => 'event/{event}', 'before' => 's5_manage_event'], function() {
    \Route::controller('/venue', 'VenueController');
    \Route::controller('/shipping', 'ShippingController');
    \Route::controller('/promotions', 'PromotionsController');
    \Route::controller('/emails', 'EmailsController');
    \Route::controller('/subusers', 'SubusersController');
    \Route::controller('/preevent', 'PreeventController');

    \Route::get('/registrations/attendee/{registration}', 'RegistrationsController@getAttendee');
    \Route::post('/registrations/attendee/{registration}', 'RegistrationsController@postAttendee');
    \Route::post('/registrations/attendee/{registration}/cancel', 'RegistrationsController@postCancel');
    \Route::post('/registrations/attendee/{registration}/refund', 'RegistrationsController@postRefund');
    \Route::post('/registrations/attendee/{registration}/transfer', 'RegistrationsController@postTransfer');
    \Route::controller('/registrations', 'RegistrationsController');

    \Route::controller('/sponsors', 'SponsorsController');
    \Route::controller('/activities', 'ActivitiesController');

    \Route::get('/', 'IndexController@getIndex');
    \Route::get('/chartdata.csv', 'IndexController@getChartdata');
    \Route::post('/update-registration-status', 'IndexController@postUpdateRegistrationStatus');
    \Route::post('/update-waitlist-status', 'IndexController@postUpdateWaitlistStatus');
    \Route::post('/notes', 'IndexController@postNotes');

    \Route::group(['before' => 's5_admin'], function(){
        \Route::controller('supplies', 'SuppliesController');
        \Route::controller('special', 'SpecialController');
    });
});