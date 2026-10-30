<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Zwei Regeln für Suchmaschinen, die für jede Anfrage gelten (SEO-Prüfung
 * 28.10.2026). Global vorgeschaltet, weil das Panel nicht über die
 * web-Gruppe läuft.
 *
 *  - /index.php/verein lieferte dieselbe Seite wie /verein, mit Status 200
 *    und einer eigenen kanonischen Adresse. Das ist doppelter Inhalt wie beim
 *    Schrägstrich am Ende (SchraegstrichEntfernen). Jetzt 301 auf die
 *    Adresse ohne /index.php.
 *  - Die Panels (/admin, /intern) gehören nicht in den Index. Die Anmeldeseite war
 *    indexierbar. Per Header statt robots.txt: Eine gesperrte Adresse kann
 *    Google trotzdem listen, nur ohne zu sehen, dass sie es nicht soll.
 */
class Suchmaschinenregeln
{
    public function handle(Request $request, Closure $next): Response
    {
        $uri = $request->getRequestUri();

        if ($request->isMethod('GET') && ($uri === '/index.php' || str_starts_with($uri, '/index.php/') || str_starts_with($uri, '/index.php?'))) {
            $ziel = substr($uri, strlen('/index.php'));

            // Absolut: redirect('/verein') hinge /index.php wieder davor, weil
            // Laravel die Basisadresse dieser Anfrage darin sieht.
            return redirect()->away($request->getSchemeAndHttpHost().(str_starts_with($ziel, '/') ? $ziel : '/'.$ziel), 301);
        }

        $antwort = $next($request);

        if ($request->is('admin', 'admin/*', 'intern', 'intern/*')) {
            $antwort->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $antwort;
    }
}
