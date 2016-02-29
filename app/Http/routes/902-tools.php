<?php

\Route::group(['namespace' => 'Manage\Tools', 'prefix' => 'tools', 'before' => 's5_manage_events', 'middleware' => ['web']], function() {
    \Route::get('', function(){ return \Redirect::to('/tools/tidbits'); });

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
});
