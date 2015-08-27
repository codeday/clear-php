<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'username' => $config['legalesign']['username'],
    'secret' => $config['legalesign']['secret'],
    'group' => $config['legalesign']['group']
];
