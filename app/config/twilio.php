<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'sid' => $config['twilio']['sid'],
    'token' => $config['twilio']['token'],
    'from' => $config['twilio']['from']
];
