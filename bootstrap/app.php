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

        /*
        |--------------------------------------------------------------------------
        | Alias de middlewares (route middleware)
        |--------------------------------------------------------------------------
        */

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserRole::class,
            'session.timeout' => \App\Http\Middleware\SessionTimeout::class,
            'provisional.validated' => \App\Http\Middleware\ProvisionalValidated::class,
            // ajoute ici les autres middlewares métier
        ]);

        /*
        |--------------------------------------------------------------------------
        | Middlewares globaux (optionnel)
        |--------------------------------------------------------------------------
        */
        // $middleware->append(\App\Http\Middleware\TrustProxies::class);

    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();
