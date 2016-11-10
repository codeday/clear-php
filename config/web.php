<?php

$config = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'local.json'), true);

return [
    'error_decrypt_key' => $config['web']['error_decrypt_key']
];
