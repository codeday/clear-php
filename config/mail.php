<?php

return [
    'driver' => 'smtp',
    'host' => 'smtp,sendgrid.net',
    'port' => 587,
    'from' => ['name' => null, 'address' => null],
    'username' => env('SENDGRID_USERNAME'),
    'password' => env('SENDGRID_PASSWORD'),
    'pretend' => false
];
