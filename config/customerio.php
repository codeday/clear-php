<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'site' => $config['customerio']['site'],
    'secret' => $config['customerio']['secret']
];
