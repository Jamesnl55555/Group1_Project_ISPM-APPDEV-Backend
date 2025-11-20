<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'register'],
    'allowed_methods' => ['*'],
    'allowed_origins_patterns' => ['/https:\/\/.*\.vercel\.app$/'],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
