<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Leitet URLs mit abschließendem Schrägstrich dauerhaft auf die Variante ohne um.
 *
 * Laravel behandelt /verein/ und /verein als dieselbe Route und liefert beide mit
 * Status 200 aus. Für Suchmaschinen sind das zwei URLs mit identischem Inhalt —
 * also doppelter Inhalt, der die Bewertung auf zwei Adressen aufteilt.
 *
 * Weil die Altseite ausschließlich Adressen mit Schrägstrich veröffentlicht hat
 * (/verein/), betrifft das jede einzelne indexierte URL. Ohne diese Umleitung
 * würde jeder bestehende Treffer bei Google auf eine Dublette zeigen.
 */
class SchraegstrichEntfernen
{
    public function handle(Request $request, Closure $next): Response
    {
        $pfad = $request->getPathInfo();

        if ($pfad !== '/' && str_ends_with($pfad, '/') && $request->isMethod('GET')) {
            $ziel = rtrim($pfad, '/');

            if ($anfrage = $request->getQueryString()) {
                $ziel .= '?'.$anfrage;
            }

            return redirect($ziel, 301);
        }

        return $next($request);
    }
}
