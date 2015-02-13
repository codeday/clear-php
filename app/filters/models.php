<?php

\Route::bind('region', function($val) {
    $region = \CodeDay\Clear\Models\Region::where('id', '=', $val)->first();
    if ($region) {
        return $region;
    } else {
        $event = \CodeDay\Clear\Models\Batch\Event::where('webname_override', '=', $val)->firstOrFail();
        $region = $event->region;
        $region->_event_override = $event;
        return $region;
    }
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

\Route::bind('sponsor', function($val) {
    return \CodeDay\Clear\Models\Batch\Event\Sponsor::where('id', '=', $val)->firstOrFail();
});

\Route::bind('email_template', function($val) {
    return \CodeDay\Clear\Models\EmailTemplate::where('id', '=', $val)->firstOrFail();
});