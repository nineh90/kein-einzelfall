<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sicherheits-Header für den Verwaltungsbereich (Prüfung der Firma, 08.10.2026).
 *
 * SicherheitsHeader hängt nur an der Gruppe `web`, das Filament-Panel hat
 * seine eigene Middleware-Liste. /admin lieferte deshalb gar keine Header,
 * ausgerechnet der Ort mit den Anfragen (Art. 9 DSGVO).
 *
 * Bewusst schlanker als die öffentliche Fassung: Deren Skript-CSP mit Nonce
 * verträgt sich nicht mit Livewire. Hier geht es um drei Dinge:
 *   - nicht einrahmbar (Clickjacking auf „Löschen“ oder Statusaktionen)
 *   - keine Adressen nach aussen (/admin/inquiries/17/edit im Referer)
 *   - nichts im Browser-Cache: Wer nach dem Abmelden „Zurück“ drückt, soll
 *     keine Anfrage mehr sehen, auch nicht auf einem geteilten Rechner.
 */
class PanelSicherheitsHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'none'");
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
