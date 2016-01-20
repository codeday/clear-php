<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'driver' => 'smtp',
    'host' => isset($config['mandrill']['smtp']['host']) ? $config['mandrill']['smtp']['host'] : 'smtp.mandrillapp.com',
    'port' => isset($config['mandrill']['smtp']['port']) ? $config['mandrill']['smtp']['port'] : 587,
    'from' => ['name' => null, 'address' => null],
    'username' => $config['mandrill']['smtp']['username'],
    'password' => $config['mandrill']['smtp']['password'],
    'pretend' => $config['mandrill']['pretend']
];
