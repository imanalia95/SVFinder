<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies — safe for shared hosting & VPS behind Nginx/Apache.
        // This ensures HTTPS is correctly detected and asset URLs are right.
        $middleware->trustProxies(at: '*');

        // Register custom middleware alias
        $middleware->alias([
            'student.auth' => \App\Http\Middleware\StudentAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
