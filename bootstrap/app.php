<?php

use App\Http\Middleware\SchraegstrichEntfernen;
use App\Support\Formularbremse;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Middleware\SicherheitsHeader;
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
        $middleware->prepend(SchraegstrichEntfernen::class);

        // Setzt die Sicherheits-Header und stellt das CSP-Nonce für die
        // Inline-Skripte bereit. Muss vor dem Rendern laufen.
        $middleware->web(append: SicherheitsHeader::class);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         * Abgelaufene Sitzung beim Absenden eines unserer Formulare (419).
         *
         * Wer lange an einer Nachricht schreibt — Pausen sind bei diesem Thema
         * normal —, bekam Laravels nackte Seite „Page Expired“, ohne
         * Notausgang und ohne seinen Text. Jetzt steht das Formular wieder da,
         * mit dem Text darin und der Bitte, ihn noch einmal abzuschicken
         * (Prüfung der Firma, 08.10.2026). Alle anderen 419 zeigen
         * errors/419.blade.php.
         */
        // Laravel hat die TokenMismatchException hier schon in einen 419
        // umgewandelt; die ursprüngliche steckt in getPrevious().
        $exceptions->render(function (HttpException $e, Request $request) {
            if (! $e->getPrevious() instanceof TokenMismatchException
                || ! $request->routeIs('anfrage.senden', 'sprache.anfrage.senden', 'beschwerde.senden', 'sprache.beschwerde.senden')) {
                return null;
            }

            return Formularbremse::hinweis($request, __('Die Seite war länger offen, deshalb konnten wir deine Nachricht aus Sicherheitsgründen nicht annehmen. Dein Text steht noch im Formular. Bitte schick ihn einfach noch einmal ab.'));
        });
    })->create();
