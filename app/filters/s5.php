<?php

use \CodeDay\Clear\Models;

\Route::filter('s5_user', function()
{
    \View::share('me', Models\User::me());
});

\Route::filter('s5_admin', function()
{
    \View::share('me', Models\User::me());
    if (!Models\User::me()->is_admin) {
        \App::abort(401);
    }
});

\Route::filter('s5_manage_event', function()
{
    \View::share('me', Models\User::me());
    if (Models\User::me()->username != \Route::input('event')->manager_username
        && !Models\User::me()->is_admin) {
        \App::abort(401);
    }
});

\Route::filter('s5_manage_events', function()
{
    \View::share('me', Models\User::me());
    if (!Models\User::me()->events->exists()) {
        \App::abort(401);
    }
});