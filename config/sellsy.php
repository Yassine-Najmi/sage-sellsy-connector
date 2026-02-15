<?php

return [
    'client_id' => env('SELLSY_CLIENT_ID'),
    'client_secret' => env('SELLSY_CLIENT_SECRET'),

    'api' => [
        'auth_url' => 'https://login.sellsy.com/oauth2/access-tokens',
        'base_url' => 'https://apifeed.sellsy.com/0/',
    ],

    'rate_limits' => [
        'per_second' => 5,
        'per_day' => 432000,
    ],

    'sync' => [
        'batch_size' => env('SYNC_BATCH_SIZE', 100), // Fetch 100 products at a time from Sage
        'max_retries' => 3,
        'retry_delay' => 5, // seconds
    ],
];
