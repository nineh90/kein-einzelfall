<?php

/*
 * Texte der Rahmen-Oberfläche: Kopf, Fuß, Notausgang, Mobil-Leiste,
 * Brotkrumen, Sprachumschalter.
 *
 * Bewusst getrennt von den Inhalten. Diese Texte gehören uns — sie beschreiben
 * die Bedienung. Die Inhalte gehören dem Verein und stehen in der Datenbank.
 * Was hier steht, dürfen wir übersetzen; Inhalte nicht.
 *
 * Deutsch ist die Vorlage: Diese Datei ist der Bestand, wie er vor der
 * Mehrsprachigkeit fest in den Blades stand. Kein Wort wurde umformuliert.
 */
return [

    'sprunglink' => 'Zum Inhalt springen',
    'menue' => 'Menü',
    'hauptnavigation' => 'Hauptnavigation',
    'hauptnavigation_mobil' => 'Hauptnavigation (mobil)',
    'schnellzugriff' => 'Schnellzugriff',
    'sie_sind_hier' => 'Sie sind hier',
    'start' => 'Start',
    'auf_dieser_seite' => 'Auf dieser Seite',
    'alle_ansehen' => 'Alle ansehen',
    'neuer_tab' => '(öffnet in neuem Tab)',

    'notausgang' => [
        'kopf' => 'Notausgang',
        'leiste' => 'Exit',
        // Die Erläuterung steht nur für Vorlesehilfen im Quelltext. Sie muss
        // sagen, was der Knopf tut, bevor jemand ihn drückt.
        'erklaerung' => '– verlässt diese Seite sofort',
    ],

    'fusszeile' => [
        'kontakt' => 'Kontakt',
        'informationen' => 'Informationen',
        'social' => 'Social Media',
        'umsetzung' => 'Umsetzung:',
    ],

    'weiterlesen' => [
        'mehr_zu' => 'Mehr zu „:bereich“',
        'auch_interessant' => 'Das könnte dich auch interessieren',
    ],

    'sprache' => [
        'auswahl' => 'Sprache',
        'wechseln_zu' => 'Sprache wechseln zu :sprache',
        'aktuell' => 'Aktuelle Sprache: :sprache',
    ],

    /*
     * Sichtbarer Rückfall.
     *
     * Fehlt eine Übersetzung, zeigen wir die Standardsprache — mit Hinweis,
     * statt still eine andere Sprache unterzuschieben. Es geht um Opferrechte,
     * Fristen und Notfallnummern; wer nicht merkt, dass er eine unübersetzte
     * Fassung liest, kann darauf falsche Schlüsse ziehen.
     */
    'rueckfall' => [
        'hinweis' => 'Diese Seite liegt noch nicht auf :ziel vor. '
            .'Sie wird auf :quelle angezeigt.',
    ],
];
