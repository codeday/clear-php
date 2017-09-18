<?php

\Route::group(['namespace' => 'Apiv2', 'prefix' => 'api/v2', 'middleware' => ['api']], function() {
    \Route::controller("oauth", "OAuthController");
    \Route::controller("users", "UsersController");
});

\Route::group(['before' => 's5_manage_events', 'middleware' => ['web']], function() {
    \Route::controller("oauth", "OAuthController");
});