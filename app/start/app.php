<?php

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

// Time tracking
$time_tracking_start = microtime(true);

// Set up Bugsnag
if (\Config::get('app.debug')) {
    \Bugsnag::setReleaseStage('development');
} else {
    \Bugsnag::setReleaseStage('release');
}

// Load Twig extensions
foreach (glob(implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'TwigFilters', '*.php'])) as $filename) {
    $class = '\CodeDay\Clear\TwigFilters\\'.basename($filename, '.php');
    \Twig::addExtension(new $class);
}

// Include markdown processor manually
include_once(implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'Markdown', "markdown.php"]));

// Global view options
\View::share('email_templates', Models\EmailTemplate::all());
\View::share('email_list_types', Services\Email::GetToListTypes());
\View::share('loaded_batch', Models\Batch::Loaded());
\View::share('all_batches', Models\Batch::orderBy('starts_at', 'ASC')->get());
\View::share('all_regions', Models\Region::all());
\View::share('all_applications', Models\Application::all());
\View::share('managed_batch', Models\Batch::Managed());

// Add CSRF protection
$csrf = csrf_token();
\View::share('csrf_token', $csrf);
\View::share('csrf', '<input type="hidden" name="_token" value="'.$csrf.'" />');

// Add version information to the view
\View::share('git', [
    'commit' => Services\GitRepository::getVersion(),
    'commit_short' => Services\GitRepository::getVersionShort(),
    'author' => Services\GitRepository::getAuthor(),
    'authored_at' => Services\GitRepository::getAuthoredTime()
]);

// Add timing information to the view
\View::share('script_millis', function() use($time_tracking_start)
{
    return round((microtime(true)-$time_tracking_start)*1000);
});

if (\Session::has('status_message')) {
    \View::share('status_message', \Session::get('status_message'));
}

if (\Session::has('error')) {
    \View::share('error', \Session::get('error'));
}

if (Models\Batch::Loaded()->id !== Models\Batch::Managed()->id) {
    \View::share('old_batch', true);
}

if (php_sapi_name() !== 'cli') {
    require(__DIR__.DIRECTORY_SEPARATOR.'web.php');
}