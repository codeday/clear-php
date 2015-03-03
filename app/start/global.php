<?php

\ClassLoader::addDirectories(array(

	app_path().'/Commands',
	app_path().'/Controllers',
	app_path().'/Models',
	app_path().'/database/seeds',

));

\Log::useFiles(storage_path().'/logs/laravel.log');

\App::error(function(Exception $exception, $code)
{
	\Log::error($exception);
});

\App::down(function()
{
	return \Response::make("Be right back!", 503);
});

$include_all_directories = ['events', 'filters'];
foreach ($include_all_directories as $directory) {
    foreach (glob(implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), $directory, "*.php"])) as $filename) {
        include_once($filename);
    }
}

\Route::group(['namespace' => '\CodeDay\Clear\Controllers'], function() {
    foreach (glob(implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'routes', "*.php"])) as $filename) {
        include_once($filename);
    }
});

$builtin_commands = ['asset:publish', 'dump-autoload', 'changes', 'clear-compiled', 'command:make',
        'config:publish', 'down', 'key:generate', 'migrate:publish', 'optimize', 'routes', 'serve',
        'tail', 'tinker', 'up', 'view:publish', 'migrate', 'migrate:make'];
if (!\App::runningInConsole() ||
    !in_array((new \Symfony\Component\Console\Input\ArgvInput())->getFirstArgument(), $builtin_commands)) {
    require_once(__DIR__.DIRECTORY_SEPARATOR.'app.php');
}