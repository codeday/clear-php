<?php

$config = json_decode(file_get_contents(dirname(dirname(dirname(__DIR__))).DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'api_key' => $config['bugsnag']['api_key']
];
