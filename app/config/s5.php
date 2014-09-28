<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'token' => $config['s5']['token'],
    'secret' => $config['s5']['secret'],
    'invite_link' => $config['s5']['invite_link']
];
