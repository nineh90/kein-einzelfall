<?php

namespace App\Http\Requests;

/**
 * Formular auf /beschwerdemanagement (KEV-98): dieselben Felder wie das
 * Kontaktformular, dazu die Wahl, wohin die Nachricht geht.
 *
 * Bewusst ohne Vorauswahl im Formular und hier Pflicht: Eine Beschwerde über
 * den Verein, die aus Versehen beim Verein landet, ist genau der Fehler, den
 * die Ombudsstelle verhindern soll.
 */
class BeschwerdeRequest extends AnfrageRequest
{
    public const WEGE = ['anfrage', 'ombudsstelle'];

    public function rules(): array
    {
        return [
            'weg' => ['required', 'in:'.implode(',', self::WEGE)],
            ...parent::rules(),
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'weg.required' => __('Bitte wähle oben, ob es um Kritik geht oder um eine Beschwerde über unseren Verein.'),
            'weg.in' => __('Bitte wähle oben, ob es um Kritik geht oder um eine Beschwerde über unseren Verein.'),
        ];
    }
}
