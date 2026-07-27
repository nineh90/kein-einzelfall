<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sicherheits-Header.
 *
 * Die Altseite liefert davon keinen einzigen aus — geprüft am 26.07.2026.
 *
 * Besonders wichtig ist hier die Content-Security-Policy: Sie legt fest, woher
 * überhaupt Inhalte geladen werden dürfen. Damit ist ausgeschlossen, dass sich
 * durch ein eingeschleustes Skript oder eine unbedacht eingefügte Einbettung
 * doch noch ein Drittanbieter in die Seite schiebt. Bei einer Seite, auf der
 * Menschen über erlebte Straftaten schreiben, ist das kein Beiwerk.
 *
 * Statt 'unsafe-inline' bekommt jede Antwort ein frisches Nonce, das die beiden
 * notwendigen Inline-Skripte (Notausgang, Darstellungs-Einstellungen) mitführen.
 * 'unsafe-inline' würde die halbe Schutzwirkung wieder aufheben.
 */
class SicherheitsHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Str::random(24);

        // Für Blade verfügbar machen, bevor die Ansicht gerendert wird.
        View::share('cspNonce', $nonce);

        // Auch die von Vite erzeugten Script-Tags brauchen das Nonce:
        // 'strict-dynamic' setzt 'self' ausser Kraft, ein Tag ohne Nonce würde
        // also blockiert — und damit Alpine und die gesamte Bedienoberfläche.
        Vite::useCspNonce($nonce);

        $response = $next($request);

        // Downloads und Dateiantworten nicht anfassen
        if (! $this->istHtml($response)) {
            return $response;
        }

        foreach ($this->header($nonce, $request) as $name => $wert) {
            $response->headers->set($name, $wert);
        }

        return $response;
    }

    private function header(string $nonce, Request $request): array
    {
        $header = [
            // Kein Erraten von Dateitypen — verhindert, dass ein Upload als
            // Skript ausgeführt wird.
            'X-Content-Type-Options' => 'nosniff',

            // Die Seite darf nicht in einen fremden Rahmen eingebettet werden
            // (Clickjacking). frame-ancestors in der CSP unten ist die moderne
            // Entsprechung; beides zusammen deckt auch ältere Browser ab.
            'X-Frame-Options' => 'DENY',

            // Beim Verlassen der Seite wird die Herkunft nicht mitgeschickt.
            // Für den Notausgang wesentlich: die Zielseite soll nicht erfahren,
            // woher jemand kommt.
            'Referrer-Policy' => 'no-referrer',

            // Funktionen, die diese Seite nicht braucht, gar nicht erst zulassen.
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), '
                .'payment=(), usb=(), interest-cohort=()',

            'Content-Security-Policy' => $this->csp($nonce),
        ];

        // HSTS nur über HTTPS senden. Über HTTP gesetzt wäre er wirkungslos,
        // und lokal würde er die Entwicklung lahmlegen.
        if ($request->secure()) {
            $header['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        return $header;
    }

    private function csp(string $nonce): string
    {
        $regeln = [
            "default-src 'self'",

            // Nonce für die notwendigen Inline-Skripte. 'strict-dynamic' erlaubt
            // dem gebündelten Skript, weitere eigene Module nachzuladen.
            "script-src 'self' 'nonce-{$nonce}' 'strict-dynamic'",

            // Für Stile bleibt 'unsafe-inline' vorerst nötig: Alpine setzt
            // style-Attribute (x-show, x-cloak) und die Darstellungs-Einstellungen
            // schreiben Custom Properties direkt auf <html>.
            "style-src 'self' 'unsafe-inline'",

            // Schriften liegen ausschliesslich lokal — genau das soll so bleiben.
            "font-src 'self'",

            "img-src 'self' data:",

            // Keine Formulare an Fremdziele
            "form-action 'self'",

            // Einbettungen nur von ausdrücklich zugelassenen Anbietern
            // (config/embeds.php). Was dort nicht steht, lädt nicht — auch
            // dann nicht, wenn jemand versehentlich einen Einbettungscode in
            // einen Textbaustein kopiert. Geladen wird ohnehin erst nach
            // Zustimmung, das hier ist der zweite Riegel.
            'frame-src '.$this->erlaubteEinbettungen(),

            "frame-ancestors 'none'",
            "base-uri 'self'",
            "object-src 'none'",
        ];

        return implode('; ', $regeln);
    }

    private function erlaubteEinbettungen(): string
    {
        $quellen = config('embeds.erlaubte_quellen', []);

        return $quellen ? implode(' ', $quellen) : "'none'";
    }

    private function istHtml(Response $response): bool
    {
        return str_contains((string) $response->headers->get('Content-Type'), 'text/html');
    }
}
