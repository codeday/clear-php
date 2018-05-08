<?php

use CodeDay\Clear\Models;

\Route::bind('region', function($val) {
    $region = Models\Region::where('id', '=', $val)->first();
    if ($region) {
        return $region;
    } else {
        $event = Models\Batch\Event
            ::select('batches_events.*')
            ->join('batches', 'batches.id', '=', 'batches_events.batch_id')
            ->where('batches.is_loaded', '=', true)
            ->where(function($group) use ($val) {
                return $group
                    ->where('webname_override', '=', $val)
                    ->orWhere(function($w2) use ($val) {
                        return $w2
                            ->where('region_id', '=', $val)
                            ->whereNull('webname_override');
                    });
            })
            ->orderBy('webname_override')
            ->orderBy('batches_events.created_at', 'DESC')
            ->firstOrFail();
        $region = $event->region;
        $region->_event_override = $event;
        return $region;
    }
});

\Route::bind('event', function($val) {
    $event = Models\Batch\Event::where('id', '=', $val)->first();

    // If we coudn't find that event, maybe it's a webname? If so, get the current one
    if (!$event)
        $event = (Models\User::IsLoggedIn() ? Models\Batch::Managed() : Models\Batch::Loaded())->EventWithWebname($val);

    if (!$event) \abort(404);

    \View::share('event', $event);
    return $event;
});

\Route::bind('batch', function($val) {
    return Models\Batch::where('id', '=', $val)->firstOrFail();
});

\Route::bind('registration', function($val) {
    return Models\Batch\Event\Registration::where('id', '=', $val)->firstOrFail();
});

\Route::bind('sponsor', function($val) {
    return Models\Batch\Event\Sponsor::where('id', '=', $val)->firstOrFail();
});

\Route::pattern('application', '[0-9A-Za-z]+');
\Route::bind('application', function($val) {
    return Models\Application::where('public', '=', $val)->firstOrFail();
});

\Route::bind('email_template', function($val) {
    return Models\EmailTemplate::where('id', '=', $val)->firstOrFail();
});

\Route::bind('agreement', function($val) {
    return Models\Agreement::where('id', '=', $val)->firstOrFail();
});
