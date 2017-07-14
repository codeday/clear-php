<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'fetch' => \PDO::FETCH_CLASS,
    'default' => 'mysql',
    'strict' => false,
    'migrations' => 'migrations',
    'connections' => $config['database']['connections'],
    'redis' => $config['redis']
];
