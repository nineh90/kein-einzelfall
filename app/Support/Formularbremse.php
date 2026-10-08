<?php

namespace App\Support;

use App\Http\Requests\AnfrageRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Wie oft man in kurzer Zeit eine Nachricht schicken kann.
 *
 * Vorher zählte `throttle:5,10` an der Route jeden Versuch, auch die mit
 * einem Tippfehler im Betreff. Wer zweimal korrigieren musste und sich dann
 * noch einmal meldete, bekam Laravels nackte 429-Seite, ohne Notausgang und
 * ohne seinen Text. Über Tor oder einen geteilten Anschluss (Beratungsstelle,
 * Frauenhaus) teilen sich ausserdem viele dieselbe Adresse (Prüfung der
 * Firma, 08.10.2026).
 *
 * Jetzt zählen nur angenommene Nachrichten. Wer zu oft schreibt, landet
 * wieder beim Formular, mit seinem Text und einem freundlichen Hinweis. Die
 * Route behält eine lockere Grenze gegen Massenversand.
 *
 * Der Schlüssel enthält die Adresse nur als Prüfsumme: Der Cache liegt in der
 * Datenbank, und IP-Adressen speichern wir nicht.
 */
class Formularbremse
{
    public const NACHRICHTEN = 5;

    public const SEKUNDEN = 600;

    public static function gesperrt(Request $request, string $formular): bool
    {
        return RateLimiter::tooManyAttempts(self::schluessel($request, $formular), self::NACHRICHTEN);
    }

    public static function zaehlen(Request $request, string $formular): void
    {
        RateLimiter::hit(self::schluessel($request, $formular), self::SEKUNDEN);
    }

    public static function zurueckZumFormular(Request $request): RedirectResponse
    {
        $minuten = max(1, (int) ceil(RateLimiter::availableIn(self::schluessel($request, self::art($request))) / 60));

        return self::hinweis($request, __('Du hast in kurzer Zeit mehrere Nachrichten geschickt. Bitte warte etwa :dauer und schick deine Nachricht dann noch einmal ab. Dein Text steht noch im Formular.', [
            'dauer' => $minuten === 1 ? __('eine Minute') : __(':anzahl Minuten', ['anzahl' => $minuten]),
        ]));
    }

    /** Zurück zum Formular, mit dem Text darin und einem Hinweis darüber. */
    public static function hinweis(Request $request, string $text): RedirectResponse
    {
        return redirect(AnfrageRequest::zurueck($request))
            ->withInput($request->except(['_token', 'gestartet_um', 'webseite']))
            ->with('formular_hinweis', $text)
            ->with('formular_hinweis_fuer', $request->input('formular'));
    }

    private static function art(Request $request): string
    {
        return $request->routeIs('*beschwerde.senden') ? 'beschwerde' : 'anfrage';
    }

    private static function schluessel(Request $request, string $formular): string
    {
        return 'formular|'.$formular.'|'.sha1((string) $request->ip());
    }
}
