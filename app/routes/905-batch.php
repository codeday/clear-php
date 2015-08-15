<?php

\Route::when('batch/*', 'csrf', ['post']);
\Route::group(['namespace' => 'Manage\Batch', 'prefix' => 'batch', 'before' => 's5_manage_events'], function() {
    \Route::get('', function(){ return \Redirect::to('/batch/change'); });
    \Route::controller('/directory', 'DirectoryController');
    \Route::controller('/change', 'ChangeController');

    \Route::group(['before' => 's5_admin'], function(){
        \Route::controller('status', 'StatusController');
        \Route::controller('promotions', 'PromotionsController');
        \Route::controller('emails', 'EmailsController');
        \Route::controller('managers', 'ManagersController');
        \Route::controller('supplies', 'SuppliesController');
        \Route::controller('tasks', 'TasksController');
        \Route::controller('revenue', 'RevenueController');
        \Route::controller('evangelists', 'EvangelistController');
        \Route::controller('shipments', 'ShipmentController');
        \Route::controller('manifests', 'ManifestController');
        \Route::controller('send-sms', 'SendSmsController');
        \Route::controller('finances', 'FinancesController');
    });
});
