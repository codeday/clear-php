<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'default' => 'redis',
    'connections' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => $config['redis']['default']['prefix'],
            'retry_after' => 30
        ]
    ],
    'failed' => [
        'database' => 'mysql',
        'table' => 'failed_jobs'
    ]
];
