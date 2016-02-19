<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'key' => $config['shipstation']['key'],
    'secret' => $config['shipstation']['secret'],
    'tags' => $config['shipstation']['tags']
];
