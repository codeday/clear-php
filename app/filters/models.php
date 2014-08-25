<?php

\Route::bind('region', function($val) {
    return \CodeDay\Clear\Models\Region::where('id', '=', $val)->firstOrFail();
});

\Route::bind('event', function($val) {
    return \CodeDay\Clear\Models\Batch\Event::where('id', '=', $val)->firstOrFail();
});

\Route::bind('batch', function($val) {
    return \CodeDay\Clear\Models\Batch::where('id', '=', $val)->firstOrFail();
});