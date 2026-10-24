<?php

namespace App\Http\Requests;

/**
 * Anmeldung zu einer Selbsthilfegruppe (KEV-226).
 *
 * Dieselben Schutzmechanismen wie beim Kontaktformular (AnfrageRequest):
 * Honigtopf und Zeitfalle statt CAPTCHA, Rücksprung zum Formular. Anders:
 * Die E-Mail-Adresse ist Pflicht, über sie kommt der Zugangslink. Der Name
 * bleibt freiwillig, ein Spitzname reicht.
 *
 * Drei Häkchen, alle Pflicht: Teilnahme- und Verschwiegenheitsvereinbarung,
 * Gruppenregeln (Taddis Wunsch) und die ausdrückliche Einwilligung in die
 * Verarbeitung, weil schon die Anmeldung eine Gesundheitsangabe ist
 * (Art. 9 Abs. 2 lit. a DSGVO).
 */
class GruppenAnmeldungRequest extends AnfrageRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:180'],
            'teilnahme' => ['accepted'],
            'gruppenregeln' => ['accepted'],
            'einwilligung' => ['accepted'],
            'herkunft' => ['nullable', 'string', 'max:120'],
            'formular' => ['nullable', 'string', 'max:60', 'regex:/^[a-z0-9-]+$/'],
            'webseite' => ['nullable', 'size:0'],
            'gestartet_um' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('Name'),
            'email' => __('E-Mail-Adresse'),
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Bitte gib deine E-Mail-Adresse an. Dorthin schicken wir dir den Zugangslink.'),
            'email.email' => __('Diese E-Mail-Adresse sieht nicht richtig aus.'),
            'teilnahme.accepted' => __('Bitte bestätige die Teilnahme- und Verschwiegenheitsvereinbarung.'),
            'gruppenregeln.accepted' => __('Bitte bestätige die Gruppenregeln.'),
            'einwilligung.accepted' => __('Ohne dein Einverständnis dürfen wir deine Anmeldung nicht speichern.'),
            'webseite.size' => __('Deine Anmeldung konnte nicht verarbeitet werden.'),
            'gestartet_um.required' => __('Bitte lade die Seite neu und versuche es erneut.'),
        ];
    }
}
