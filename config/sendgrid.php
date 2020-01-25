<?php

return [
  "username" => env('SENDGRID_USERNAME'),
  "password" => env('SENDGRID_PASSWORD'),
  "api_key" => env('SENDGRID_API_KEY'),
  "asm" => [
    "marketing" => env('SENDGRID_ASM_MARKETING'),
    "transactional" => env('SENDGRID_ASM_TRANSACTIONAL'),
  ]
];
