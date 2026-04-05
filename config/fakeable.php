<?php

return [
    'enabled' => env('FAKEABLE_ENABLED', true),

    'allowed_hosts' => [
        '*.test',
        '*.dev',
        'localhost',
    ],

    'locale' => 'en_US',

    'show_indicator' => true,
];
