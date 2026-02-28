<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This controls the default mailer used by the application for all emails.
    | For Brevo REST API, we set it to 'brevo' here.
    |
    */

    'default' => env('MAIL_MAILER', 'brevo'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you can define all mailers used by your application. We add a
    | custom 'brevo' mailer that uses the official Brevo PHP SDK.
    |
    */

    'mailers' => [

        'brevo' => [
            'transport' => 'brevo',
            'api_key' => env('BREVO_API_KEY'), // REST API key, not SMTP key!
        ],

        // You can keep other mailers if needed
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | All emails sent by your application will use this sender by default.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'jamesnl55555@gmail.com'),
        'name' => env('MAIL_FROM_NAME', '88Chocolates'),
    ],

];