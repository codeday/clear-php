<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'driver' => 'smtp',
    'host' => 'smtp.mandrillapp.com',
    'port' => 587,
    'from' => ['name' => null, 'address' => null],
    'username' => $config['mandrill']['smtp']['username'],
    'password' => $config['mandrill']['smtp']['password'],
    'pretend' => $config['mandrill']['pretend']
];
