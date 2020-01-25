<?php

return [
  "key" => env("AWS_KEY"),
  "secret" => env("AWS_SECRET"),
  "region" => env("AWS_REGION"),
  "s3" => [
    "waiverBucket" => env("AWS_WAIVER_BUCKET"),
    "assetsBucket" => env("AWS_ASSETS_BUCKET"),
    "assetsUrl" => env("AWS_ASSETS_URL"),
  ]
];
