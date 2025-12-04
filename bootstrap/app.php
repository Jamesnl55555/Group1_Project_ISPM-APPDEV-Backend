<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_AWS_ELB);

        // Make API stateful for Sanctum
        $middleware->statefulApi();

        // Sanctum must be FIRST in the web stack
        $middleware->web(
            prepend: [
                \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            ],
            append: [
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
                \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            ]
        );

        // CORS handler
        $middleware->append([
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // Exclude API routes from CSRF
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom exception handling if any
    })
    ->create();
