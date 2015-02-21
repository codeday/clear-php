<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'access_key' => $config['ups']['access_key'],
    'user_id' => $config['ups']['user_id'],
    'password' => $config['ups']['password'],
    'account' => $config['ups']['account'],
    'integration' => $config['ups']['integration'],
    'ship_from' => $config['ups']['ship_from']
];
