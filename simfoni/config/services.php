<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    
    'ssobps' => [
        'id' => env('SSO_BPS_CLIENT_ID'),
        'secret' => env('SSO_BPS_CLIENT_SECRET'),
        'uri' => env('SSO_BPS_REDIRECT_URI'),
    ],

    // FastAPI (main.py) yang menjalankan model IndoBERT/idT5/IndoBART untuk menu Otomatisasi
    'inference' => [
        'url' => env('INFERENCE_API_URL', 'http://localhost:8001'),
        'timeout' => (int) env('INFERENCE_API_TIMEOUT', 120), // detik, per request (1 berita 3 model / 1 MDS)
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
