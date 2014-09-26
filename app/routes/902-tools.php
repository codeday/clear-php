<?php

\Route::group(['namespace' => 'Manage\Tools', 'prefix' => 'tools'], function() {

    \Route::get('', function(){ return \Redirect::to('/tools/promotions'); });

    \Route::group(['before' => 's5_admin'], function(){
        \Route::controller('promotions', 'PromotionsController');
    });
});