<?php

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'firebase' => [
        'sa_path' => env('FIREBASE_SA_PATH'),
    ],

    'tmdb' => [
        'key' => env('TMDB_API_KEY'),
    ],

];
