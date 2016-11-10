<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'driver' => 'smtp',
    'host' => 'smtp,sendgrid.net',
    'port' => 587,
    'from' => ['name' => null, 'address' => null],
    'username' => $config['sendgrid']['username'],
    'password' => $config['sendgrid']['password'],
    'pretend' => false
];
