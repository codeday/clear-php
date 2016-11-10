<?php

\Route::group(['namespace' => 'Manage\Settings', 'prefix' => 'settings', 'before' => 's5_admin', 'middleware' => ['web']], function() {
    \Route::get('batches', 'BatchesController@getIndex');
    \Route::get('batches/create', 'BatchesController@getCreate');
    \Route::post('batches/create', 'BatchesController@postCreate');
    \Route::get('batches/{batch}', 'BatchesController@getEdit');
    \Route::post('batches/{batch}', 'BatchesController@postEdit');
    \Route::post('batches/{batch}/updateregion', 'BatchesController@postUpdateRegion');
    \Route::post('batches/{batch}/updateregionsettings', 'BatchesController@postUpdateRegionSettings');
    \Route::get('batches/{batch}/delete', 'BatchesController@getDelete');
    \Route::post('batches/{batch}/delete', 'BatchesController@postDelete');
    \Route::get('batches/{batch}/load', 'BatchesController@getLoad');
    \Route::post('batches/{batch}/load', 'BatchesController@postLoad');
    \Route::post('batches/{batch}/addsupplies', 'BatchesController@postAddSupplies');
    \Route::post('batches/{batch}/deletesupplies', 'BatchesController@postDeleteSupplies');

    \Route::get('regions', 'RegionsController@getIndex');
    \Route::get('regions/create', 'RegionsController@getCreate');
    \Route::post('regions/create', 'RegionsController@postCreate');
    \Route::get('regions/{region}', 'RegionsController@getEdit');
    \Route::post('regions/{region}', 'RegionsController@postEdit');

    \Route::get('agreements', 'AgreementsController@getIndex');
    \Route::get('agreements/create', 'AgreementsController@getCreate');
    \Route::post('agreements/create', 'AgreementsController@postCreate');
    \Route::get('agreements/{agreement}', 'AgreementsController@getEdit');
    \Route::post('agreements/{agreement}', 'AgreementsController@postEdit');

    \Route::get('email-templates', 'EmailTemplatesController@getIndex');
    \Route::post('email-templates/new', 'EmailTemplatesController@postNew');
    \Route::post('email-templates/{email_template}/delete', 'EmailTemplatesController@postDelete');

    \Route::controller('sms', 'SmsController');

    \Route::get('', function() {
        return \Redirect::to('/settings/batches');
    });
});