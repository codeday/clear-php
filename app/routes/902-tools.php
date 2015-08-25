<?php

\Route::when('tools/*', 'csrf', ['post']);
\Route::group(['namespace' => 'Manage\Tools', 'prefix' => 'tools', 'before' => 's5_manage_events'], function() {
    \Route::get('', function(){ return \Redirect::to('/tools/attendee'); });
    \Route::controller('/attendee', 'AttendeeController');

    \Route::get('/tidbits/{region}', 'TidbitsController@getRegion');
    \Route::controller('/tidbits', 'TidbitsController');

    \Route::post('/applications/new', 'ApplicationsController@postNew');
    \Route::get('/applications/{application}', 'ApplicationsController@getEdit');
    \Route::post('/applications/{application}', 'ApplicationsController@postEdit');
    \Route::post('/applications/{application}/webhook', 'ApplicationsController@postWebhook');
    \Route::post('/applications/{application}/webhook/delete', 'ApplicationsController@postWebhookDelete');
    \Route::controller('/applications', 'ApplicationsController');
    \Route::controller('banlist', 'BanlistController');

    \Route::group(['before' => 's5_admin'], function(){
        \Route::controller('giftcards', 'GiftCardsController');
        \Route::controller('query', 'QueryController');
    });

    \Route::get('/checkin', function() { return \Redirect::to('/dayof/checkin'); });
    \Route::get('/deck', function() { return \Redirect::to('/dayof/deck'); });
});
