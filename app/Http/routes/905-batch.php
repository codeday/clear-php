<?php

\Route::group(['namespace' => 'Manage\Batch', 'prefix' => 'batch', 'before' => 's5_manage_events', 'middleware' => ['web']], function() {
    \Route::get('', function(){ return \Redirect::to('/batch/change'); });
    \Route::controller('/directory', 'DirectoryController');
    \Route::controller('/change', 'ChangeController');
    \Route::controller('/bios', 'StaffBiosController');

    \Route::group(['before' => 's5_admin'], function(){
        \Route::controller('promotions', 'PromotionsController');
        \Route::controller('emails', 'EmailsController');
        \Route::controller('managers', 'ManagersController');
        \Route::controller('supplies', 'SuppliesController');
        \Route::controller('tasks', 'TasksController');
        \Route::controller('evangelists', 'EvangelistController');
        \Route::controller('shipments', 'ShipmentController');
        \Route::controller('send-sms', 'SendSmsController');
        \Route::controller('news', 'NewsController');
        \Route::controller('csv', 'CsvController');
    });
});
