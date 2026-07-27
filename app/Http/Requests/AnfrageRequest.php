<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AnfrageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Name und E-Mail sind bewusst freiwillig — anonyme Kontaktaufnahme
            // ist bei dieser Zielgruppe ein echtes Bedürfnis. Auf der Altseite
            // sind beide Pflicht; das ist eine bewusste Abweichung.
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:180'],
            'betreff' => ['required', 'string', 'min:3', 'max:200'],
            'nachricht' => ['required', 'string', 'min:10', 'max:8000'],
            'einwilligung' => ['accepted'],

            'herkunft' => ['nullable', 'string', 'max:120'],

            // Honigtopf: muss leer bleiben
            'webseite' => ['nullable', 'size:0'],
            'gestartet_um' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'email' => 'E-Mail-Adresse',
            'betreff' => 'Betreff',
            'nachricht' => 'Nachricht',
            'einwilligung' => 'Einwilligung',
        ];
    }

    public function messages(): array
    {
        // Freundlich und in Du-Form — der Rest der Seite spricht auch so,
        // und wer hier schreibt, ist oft ohnehin angespannt.
        return [
            'betreff.required' => 'Bitte gib einen Betreff an.',
            'betreff.min' => 'Der Betreff ist sehr kurz — magst du ihn etwas ausführlicher fassen?',
            'nachricht.required' => 'Bitte schreib uns ein paar Zeilen.',
            'nachricht.min' => 'Die Nachricht ist sehr kurz. Schreib gern etwas mehr.',
            'nachricht.max' => 'Die Nachricht ist zu lang. Bitte kürze sie etwas oder schreib uns direkt eine E-Mail.',
            'email.email' => 'Diese E-Mail-Adresse sieht nicht richtig aus. Du kannst das Feld auch frei lassen.',
            'einwilligung.accepted' => 'Ohne dein Einverständnis dürfen wir deine Nachricht nicht speichern.',
            'webseite.size' => 'Deine Anfrage konnte nicht verarbeitet werden.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Zeitfalle: Ein ausgefülltes Formular in unter drei Sekunden ist
            // menschlich kaum zu schaffen. Fängt einfache Bots ab, ohne dass
            // jemand ein CAPTCHA lösen muss.
            $start = $this->input('gestartet_um');

            if (! $start) {
                return;
            }

            try {
                $sekunden = now()->timestamp - (int) decrypt($start);
            } catch (\Throwable) {
                // Manipulierter oder abgelaufener Wert
                $validator->errors()->add('nachricht', 'Bitte lade die Seite neu und versuche es erneut.');

                return;
            }

            if ($sekunden < 3) {
                $validator->errors()->add('nachricht', 'Bitte lade die Seite neu und versuche es erneut.');
            }
        });
    }
}
