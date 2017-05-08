<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'webhook_url' => $config['slack']['webhook_url'],
    'client_id' => $config['slack']['client_id'],
    'client_secret' => $config['slack']['client_secret'],
    'internal_app' => $config['slack']['internal_app']
];
