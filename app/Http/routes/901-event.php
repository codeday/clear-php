<?php

use CodeDay\Clear\Models;

\Route::get('event/{event}/registrations/csv', 'Manage\Event\RegistrationsController@getCsv');

\Route::any('event/my/{path}', 'Manage\Event\IndexController@getMyEvent')->where('path', '.+');

\Route::filter('check_agreement', function() {
    $event = \Route::input('event');
    if ($event->manager_username == Models\User::me()->username
        && $event->agreement_id && !$event->agreement_signed_url) {
        return \Redirect::to('/event/'.$event->id.'/agreement');
    }
});

\Route::group(['namespace' => 'Manage\Event', 'prefix' => 'event/{event}', 'before' => 's5_manage_event', 'middleware' => ['web']], function() {

    \Route::group(['before' => 'check_manager'], function() {
        \Route::controller('/agreement', 'AgreementController');
    });

    \Route::group(['before' => 'check_agreement'], function() {
        \Route::controller('/venue', 'VenueController');
        \Route::controller('/shipping', 'ShippingController');
        \Route::controller('/promotions', 'PromotionsController');
        \Route::controller('/emails', 'EmailsController');
        \Route::controller('/subusers', 'SubusersController');
        \Route::controller('/preevent', 'PreeventController');
        \Route::controller('/subscriptions', 'SubscriptionsController');
        \Route::controller('/overview', 'OverviewController');

        \Route::get('/registrations/attendee/{registration}', 'RegistrationsController@getAttendee');
        \Route::post('/registrations/attendee/{registration}', 'RegistrationsController@postAttendee');
        \Route::post('/registrations/attendee/{registration}/cancel', 'RegistrationsController@postCancel');
        \Route::post('/registrations/attendee/{registration}/refund', 'RegistrationsController@postRefund');
        \Route::post('/registrations/attendee/{registration}/transfer', 'RegistrationsController@postTransfer');
        \Route::post('/registrations/attendee/{registration}/webhook', 'RegistrationsController@postWebhook');
        \Route::post('/registrations/attendee/{registration}/resend', 'RegistrationsController@postResend');
        \Route::post('/registrations/attendee/{registration}/waiver', 'RegistrationsController@postWaiver');
        \Route::controller('/registrations/csv', 'CsvController');
        \Route::controller('/registrations', 'RegistrationsController');

        \Route::get('/sponsors/{sponsor}/edit', 'SponsorsController@getEdit');
        \Route::post('/sponsors/{sponsor}/edit', 'SponsorsController@postEdit');
        \Route::post('/sponsors/{sponsor}/delete', 'SponsorsController@postDelete');
        \Route::controller('/sponsors', 'SponsorsController');

        \Route::controller('/activities', 'ActivitiesController');

        \Route::get('/', 'IndexController@getIndex');
        \Route::get('/chartdata.csv', 'IndexController@getChartdata');
        \Route::post('/update-registration-status', 'IndexController@postUpdateRegistrationStatus');
        \Route::post('/update-waitlist-status', 'IndexController@postUpdateWaitlistStatus');
        \Route::post('/notes', 'IndexController@postNotes');

        \Route::group(['before' => 's5_admin'], function () {
            \Route::controller('supplies', 'SuppliesController');
            \Route::controller('special', 'SpecialController');
        });
    });
});
