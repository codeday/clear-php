<?php

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

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

// Set up Bugsnag
if (\Config::get('app.debug')) {
    \Bugsnag::setReleaseStage('development');
} else {
    \Bugsnag::setReleaseStage('release');
}

// XSS, CSRF, etc protection
\App::after(function($request, $response)
{
    $csp = "default-src 'self'; script-src 'unsafe-eval' 'unsafe-inline' 'self' https://*.googleapis.com https://cdnjs.cloudflare.com"
         . " http://code.jquery.com https://code.jquery.com https://*.gstatic.com; object-src 'self'; style-src 'self' 'unsafe-inline'"
         . " https://*.googleapis.com https://*.gstatic.com; img-src *; media-src *; frame-src 'self';"
         . " font-src 'self' https://*.googleapis.com https://*.gstatic.com; connect-src *";

    if (\Request::server("HTTP_HOST") === 'clear.codeday.org') {
        $response->headers->set('Strict-Transport-Security', '2,592,000');
    }

    $response->headers->set('X-Frame-Options', 'deny');
    $response->headers->set('Frame-Options', 'deny');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('Content-Security-Policy', $csp);
    $response->headers->set('X-Content-Security-Policy', $csp);
    $response->headers->set('X-WebKit-CSP', $csp);
});


$include_all_directories = ['events', 'filters'];
foreach ($include_all_directories as $directory) {
    foreach (glob(implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), $directory, "*.php"])) as $filename) {
        include_once($filename);
    }
}

// Load Twig extensions
foreach (glob(implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'TwigFilters', '*.php'])) as $filename) {
    $class = '\CodeDay\Clear\TwigFilters\\'.basename($filename, '.php');
    \Twig::addExtension(new $class);
}

// Global view options
\View::share('email_templates', Models\EmailTemplate::all());
\View::share('email_list_types', Services\Email::GetToListTypes());
\View::share('loaded_batch', Models\Batch::Loaded());
\View::share('all_batches', Models\Batch::orderBy('starts_at', 'ASC')->get());
\View::share('all_regions', Models\Region::all());
\View::share('all_applications', Models\Application::all());
\View::share('managed_batch', Models\Batch::Managed());

if (\Session::has('status_message')) {
    \View::share('status_message', \Session::get('status_message'));
}

if (\Session::has('error')) {
    \View::share('error', \Session::get('error'));
}

if (Models\Batch::Loaded()->id !== Models\Batch::Managed()->id) {
    \View::share('old_batch', true);
}

\Route::group(['namespace' => '\CodeDay\Clear\Controllers'], function() {
    foreach (glob(implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'routes', "*.php"])) as $filename) {
        include_once($filename);
    }
});

