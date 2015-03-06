<?php

\ClassLoader::addDirectories(array(

	app_path().'/Commands',
	app_path().'/Controllers',
	app_path().'/Models',
	app_path().'/database/seeds',

));

\Log::useFiles(storage_path().'/logs/laravel.log');

\App::down(function()
{
	return \Response::make("Be right back!", 503);
});

if(\Config::get('app.debug')){
	\App::error(function(Exception $exception, $code)
	{
		\Log::error($exception);
	});
}else{
	\App::error(function(Exception $exception, $code)
	{
		\Log::error($exception);
		return Response::view('errors.'.$code, array(), $code);
	});
}

$include_all_directories = ['events', 'filters'];
foreach ($include_all_directories as $directory) {
    foreach (glob(implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), $directory, "*.php"])) as $filename) {
        include($filename); // We use include instead of include_once anywhere that doesn't define a class because if we
                            // don't, Laravel breaks when we try to run tests.
    }
}

\Route::group(['namespace' => '\CodeDay\Clear\Controllers'], function() {
    foreach (glob(implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'routes', "*.php"])) as $filename) {
        include($filename);
    }
});

$builtin_commands = ['asset:publish', 'dump-autoload', 'changes', 'clear-compiled', 'command:make',
        'config:publish', 'down', 'key:generate', 'migrate:publish', 'optimize', 'routes', 'serve',
        'tail', 'tinker', 'up', 'view:publish', 'migrate', 'migrate:make', 'migrate:rollback',
        'migrate:refresh', 'migrate:reset', 'test', 'db:seed'];
if (!\App::runningInConsole() ||
    !in_array((new \Symfony\Component\Console\Input\ArgvInput())->getFirstArgument(), $builtin_commands)) {
    require(__DIR__.DIRECTORY_SEPARATOR.'app.php');
}
