<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'token' => $config['taxjar']['token'],
    'category' => $config['taxjar']['category']
];
