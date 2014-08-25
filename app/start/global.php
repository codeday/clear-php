<?php

ClassLoader::addDirectories(array(

	app_path().'/Commands',
	app_path().'/Controllers',
	app_path().'/Models',
	app_path().'/database/seeds',

));

Log::useFiles(storage_path().'/logs/laravel.log');

App::error(function(Exception $exception, $code)
{
	Log::error($exception);
});

App::down(function()
{
	return Response::make("Be right back!", 503);
});


$include_all_directories = ['events', 'filters'];
foreach ($include_all_directories as $directory) {
    foreach (glob(implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), $directory, "*.php"])) as $filename) {
        include_once($filename);
    }
}


\Route::group(['namespace' => '\CodeDay\Clear\Controllers'], function() {
    \View::share('loaded_batch', \CodeDay\Clear\Models\Batch::Loaded());
    \View::share('all_batches', \CodeDay\Clear\Models\Batch::orderBy('starts_at', 'ASC')->get());
    \View::share('all_regions', \CodeDay\Clear\Models\Region::all());


    foreach (glob(implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'routes', "*.php"])) as $filename) {
        include_once($filename);
    }
});

