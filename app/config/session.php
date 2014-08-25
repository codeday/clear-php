<?php

return [
    'driver' => 'memcached',
    'lifetime' => 60*24*31*3,
    'expire_on_close' => false,
    'cookie' => 'codeday_session',
    'domain' => null,
    'path' => '/',
    'secure' => false,
    'lottery' => [2, 100],
];
