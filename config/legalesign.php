<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'userid' => $config['legalesign']['userid'],
    'secret' => $config['legalesign']['secret'],
    'group' => $config['legalesign']['group'],
    'waiver' => $config['legalesign']['waiver']
];
