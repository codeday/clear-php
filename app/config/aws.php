<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'key' => $config['aws']['key'],
    'secret' => $config['aws']['secret'],
    's3' => $config['aws']['s3']
];
