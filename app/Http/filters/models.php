<?php

use CodeDay\Clear\Models;

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
    $event = \CodeDay\Clear\Models\Batch\Event::where('id', '=', $val)->first();

    // If we coudn't find that event, maybe it's a webname? If so, get the current one
    if (!$event)
        $event = (Models\User::IsLoggedIn() ? Models\Batch::Managed() : Models\Batch::Loaded())->EventWithWebname($val);

    if (!$event) \abort(404);

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

\Route::pattern('application', '[0-9A-Za-z]+');
\Route::bind('application', function($val) {
    return \CodeDay\Clear\Models\Application::where('public', '=', $val)->firstOrFail();
});

\Route::bind('email_template', function($val) {
    return \CodeDay\Clear\Models\EmailTemplate::where('id', '=', $val)->firstOrFail();
});

\Route::bind('agreement', function($val) {
    return \CodeDay\Clear\Models\Agreement::where('id', '=', $val)->firstOrFail();
});
