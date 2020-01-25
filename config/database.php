<?php

return [
    'fetch' => \PDO::FETCH_CLASS,
    'default' => 'mysql',
    'strict' => true,
    'migrations' => 'migrations',
    'connections' => [
      'mysql' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE', 'clear'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD'),
        'charset' => 'utf8',
        'collation' => 'utf8_bin',
        'prefix' => '',
        'strict' => false,
      ]
    ],
    'redis' => [
      'client' => 'phpredis',
      'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 0),
        'prefix' => env('REDIS_PREFIX', 'clear_'),
        'presistent' => true,
      ]
    ]
];
