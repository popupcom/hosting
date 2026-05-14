<?php

return [
    'webhook_hmac_secrets' => [
        'moco' => env('WEBHOOK_HMAC_SECRET_MOCO'),
        'managewp' => env('WEBHOOK_HMAC_SECRET_MANAGEWP'),
        'autodns' => env('WEBHOOK_HMAC_SECRET_AUTODNS'),
    ],

    'webhook_bearer_tokens' => [
        'moco' => env('WEBHOOK_BEARER_TOKEN_MOCO'),
        'managewp' => env('WEBHOOK_BEARER_TOKEN_MANAGEWP'),
        'autodns' => env('WEBHOOK_BEARER_TOKEN_AUTODNS'),
    ],
];
