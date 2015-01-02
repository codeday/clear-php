<?php

\Route::group(['namespace' => 'Manage\Tools', 'prefix' => 'tools', 'before' => 's5_manage_events'], function() {

    \Route::get('', function(){ return \Redirect::to('/tools/attendee'); });
    \Route::controller('/attendee', 'AttendeeController');
    \Route::controller('/checkin', 'CheckinController');
    \Route::controller('/deck', 'DeckController');

    \Route::group(['before' => 's5_admin'], function(){
        \Route::controller('promotions', 'PromotionsController');
        \Route::controller('emails', 'EmailsController');
        \Route::controller('evangelists', 'EvangelistController');
        \Route::controller('shipments', 'ShipmentController');
        \Route::controller('manifests', 'ManifestController');
    });
});