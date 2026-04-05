<?php

return [
    'enabled' => env('FAKEABLE_ENABLED', true),

    // Glob patterns (fnmatch): *.test matches myapp.test and sub.myapp.test.
    'allowed_hosts' => [
        '*.test',
        '*.dev',
        'localhost',
    ],

    'locale' => 'en_US',

    'show_indicator' => true,
];
