<?php

use CodeDay\Clear\Models;
use CodeDay\Clear\Services;

// TODO: Move these to a proper location
// Include markdown processor manually
include_once(implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'Markdown', "markdown.php"]));


$builtin_commands = ['asset:publish', 'dump-autoload', 'changes', 'clear-compiled', 'command:make',
    'config:publish', 'down', 'key:generate', 'migrate:publish', 'optimize', 'routes', 'serve',
    'tail', 'tinker', 'up', 'view:publish', 'migrate', 'migrate:make', 'migrate:rollback',
    'migrate:refresh', 'migrate:reset', 'test', 'db:seed'];
if (!\App::runningInConsole() ||
    !in_array((new \Symfony\Component\Console\Input\ArgvInput())->getFirstArgument(), $builtin_commands)) {
    // Global view options
    \View::share('email_templates', Models\EmailTemplate::all());
    \View::share('email_list_types', Services\Email::GetToListTypes());
    \View::share('loaded_batch', Models\Batch::Loaded());
    \View::share('all_regions', Models\Region::all());
    \View::share('managed_batch', Models\Batch::Managed());
}


$include_all_directories = ['events', 'filters'];
foreach ($include_all_directories as $directory) {
    foreach (glob(implode(DIRECTORY_SEPARATOR, [__DIR__, $directory, "*.php"])) as $filename) {
        include($filename); // We use include instead of include_once anywhere that doesn't define a class because if we
        // don't, Laravel breaks when we try to run tests.
    }
}

foreach (glob(implode(DIRECTORY_SEPARATOR, [__DIR__, 'routes', "*.php"])) as $filename) {
            include($filename);
}
