<?php

\Route::group(['namespace' => 'Manage\Tools', 'prefix' => 'tools', 'before' => 's5_manage_events'], function() {

    \Route::get('', function(){ return \Redirect::to('/tools/promotions'); });

    \Route::group(['before' => 's5_admin'], function(){
        \Route::controller('promotions', 'PromotionsController');
    });
});