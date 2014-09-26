<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'secret' => $config['stripe']['secret'],
    'public' => $config['stripe']['public']
];
