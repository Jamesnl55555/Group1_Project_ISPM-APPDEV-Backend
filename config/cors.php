<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [],
    'allowed_origins_patterns' => ['https://.*\.vercel\.app'],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];

