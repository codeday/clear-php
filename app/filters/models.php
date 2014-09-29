<?php

\Route::bind('region', function($val) {
    return \CodeDay\Clear\Models\Region::where('id', '=', $val)->firstOrFail();
});

\Route::bind('event', function($val) {
    $event = \CodeDay\Clear\Models\Batch\Event::where('id', '=', $val)->firstOrFail();
    \View::share('event', $event);
    return $event;
});

\Route::bind('batch', function($val) {
    return \CodeDay\Clear\Models\Batch::where('id', '=', $val)->firstOrFail();
});

\Route::bind('registration', function($val) {
    return \CodeDay\Clear\Models\Batch\Event\Registration::where('id', '=', $val)->firstOrFail();
});

\Route::bind('email_template', function($val) {
    return \CodeDay\Clear\Models\EmailTemplate::where('id', '=', $val)->firstOrFail();
});