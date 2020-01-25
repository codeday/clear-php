<?php

return [
    'default' => 'redis',
    'connections' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_PREFIX', 'clear_'),
            'retry_after' => 30
        ]
    ],
    'failed' => [
        'database' => 'mysql',
        'table' => 'failed_jobs'
    ]
];
