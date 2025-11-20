<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'register'],
    'allowed_origins' => ['https://ispmappdevfrontend.vercel.app'],
    'allowed_methods' => ['*'],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
