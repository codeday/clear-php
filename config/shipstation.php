<?php

return [
  'key' => env('SHIPSTATION_KEY'),
  'secret' => env('SHIPSTATION_SECRET'),
  'tags' => [
    'event_supplies' => env('SHIPSTATION_TAG_EVENT'),
    'general_supplies' => env('SHIPSTATION_TAG_GENERAL'),
  ],
];
