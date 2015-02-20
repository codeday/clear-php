<?php

\Route::group(['namespace' => 'Email', 'prefix' => 'e'], function() {
    \Route::controller('', 'IndexController');
});