<?php
return [

    'paths' => [
    'api/*', 
    '/login',
    '/logout',
    '/register',
    '/forgot-password',
    '/reset-password',
    'sanctum/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://ispmappdevfrontend.vercel.app',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];


