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
        // Muss ganz vorne laufen: /verein/ und /verein sind für Laravel dieselbe
        // Route, für Suchmaschinen aber doppelter Inhalt.
        $middleware->prepend(\App\Http\Middleware\SchraegstrichEntfernen::class);

        // Setzt die Sicherheits-Header und stellt das CSP-Nonce für die
        // Inline-Skripte bereit. Muss vor dem Rendern laufen.
        $middleware->web(append: \App\Http\Middleware\SicherheitsHeader::class);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
