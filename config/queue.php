<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'default' => 'beanstalkd',
    'connections' => $config['queue']['connections'],
    'failed' => [
        'database' => 'mysql',
        'table' => 'failed_jobs'
    ]
];
