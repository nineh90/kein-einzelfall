<?php

namespace App\Http\Controllers;

use App\Http\Requests\BeschwerdeRequest;
use App\Mail\NachrichtAnOmbudsstelle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Das Formular auf /beschwerdemanagement (KEV-98). Ein Formular, zwei Wege:
 * Wer schreibt, wählt oben, worum es geht.
 *
 *  - anfrage       Kritik von außen („Du kommst nicht weiter …“). Landet wie
 *                  jede Anfrage verschlüsselt im Verwaltungsbereich.
 *  - ombudsstelle  Beschwerde über den Verein. Geht laut Taddis Text an eine
 *                  unabhängige Ombudsstelle, und deshalb nicht in den
 *                  Verwaltungsbereich: Dort lesen genau die Menschen mit, um
 *                  die es in der Beschwerde gehen kann. Sie geht als E-Mail an
 *                  die Ombudsstelle und wird auf der Website nirgends
 *                  gespeichert, auch nicht im Log.
 *
 * Dieselben Regeln wie beim Kontaktformular (AnfrageRequest): Name und
 * E-Mail freiwillig, Honigtopf und Zeitfalle statt CAPTCHA.
 */
class BeschwerdeController extends Controller
{
    public function store(BeschwerdeRequest $request, AnfrageController $anfragen)
    {
        $formular = $request->input('formular');
        $zurueck = BeschwerdeRequest::mitSprungziel(url()->previous(), $formular);

        if ($request->input('weg') === 'anfrage') {
            $herkunft = trim($request->string('herkunft')->limit(100, '')->value().' · Kritik', ' ·');
            $anfrage = $anfragen->annehmen($request, $herkunft);

            return redirect($zurueck)
                ->with('versendet_von', $formular)
                ->with('anfrage_versendet', AnfrageController::bestaetigung($anfrage));
        }

        if (! $this->versandMoeglich()) {
            return $this->fehlgeschlagen($zurueck, $formular);
        }

        try {
            Mail::to(config('mail.ombudsstelle_an'))->send(new NachrichtAnOmbudsstelle(
                name: $request->filled('name') ? $request->string('name')->trim()->value() : null,
                email: $request->filled('email') ? $request->string('email')->trim()->value() : null,
                betreff: $request->string('betreff')->trim()->value(),
                nachricht: $request->string('nachricht')->trim()->value(),
            ));
        } catch (\Throwable $e) {
            // Nur die Fehlermeldung des Mailservers, nichts aus dem Formular.
            Log::error('Nachricht an die Ombudsstelle nicht verschickt', ['fehler' => $e->getMessage()]);

            return $this->fehlgeschlagen($zurueck, $formular);
        }

        return redirect($zurueck)
            ->with('versendet_von', $formular)
            ->with(
                'anfrage_versendet',
                $request->filled('email')
                    ? 'Sie ist an die Ombudsstelle gegangen. Die Antwort kommt direkt von dort an deine E-Mail-Adresse.'
                    : 'Sie ist an die Ombudsstelle gegangen. Da du keine E-Mail-Adresse angegeben '
                      .'hast, kann sie dir nicht direkt antworten.'
            );
    }

    /**
     * Auf dem Server nur mit echtem Mailversand.
     *
     * Mit MAIL_MAILER=log schriebe Laravel die ganze E-Mail samt Beschwerde
     * nach storage/logs, im Klartext. Dann lieber ehrlich sagen, dass es nicht
     * geht, und auf die Adresse verweisen. Lokal bleibt es erlaubt, dort ist
     * das Log der Weg, das Formular auszuprobieren.
     */
    private function versandMoeglich(): bool
    {
        return ! (app()->isProduction() && config('mail.default') === 'log');
    }

    /** Zurück zum Formular, mit dem Text darin und dem Weg daneben. */
    private function fehlgeschlagen(string $zurueck, ?string $formular)
    {
        return redirect($zurueck)
            ->withInput()
            ->with('versand_fehlgeschlagen', $formular ?? 'f');
    }
}
