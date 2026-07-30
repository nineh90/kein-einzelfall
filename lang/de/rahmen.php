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

    'fehler' => [
        'titel_404' => 'Diese Seite gibt es nicht',
        'lead_404' => 'Vielleicht hat sich die Adresse geändert, oder es hat sich ein Tippfehler '
            .'eingeschlichen. Hier sind ein paar Wege weiter.',
        'titel_500' => 'Da ist bei uns etwas schiefgegangen',
        'lead_500' => 'Der Fehler liegt nicht bei dir. Versuch es bitte in ein paar Minuten noch '
            .'einmal — die Nummern unten erreichst du davon unabhängig.',
        'titel_503' => 'Wir sind gleich zurück',
        'lead_503' => 'An der Seite wird gerade gearbeitet. Die Nummern unten erreichst du '
            .'davon unabhängig.',
        'suche' => 'Beiträge durchsuchen',
        'suche_knopf' => 'Suchen',
        'wohin' => 'Wohin möchtest du?',
        'zur_startseite' => 'Zur Startseite',
    ],

    /*
     * Leichte Sprache.
     *
     * Die Beschriftungen folgen den Regeln der Leichten Sprache: kurze Saetze,
     * keine Fremdwoerter, aktive Formulierung. „Alltagssprache“ statt
     * „Standardfassung“ — „Standard“ ist selbst ein schweres Wort.
     */
    'leichte_sprache' => [
        'name' => 'Leichte Sprache',
        'zu_leichter_sprache' => 'Diese Seite in Leichter Sprache',
        'zur_standardfassung' => 'Diese Seite in Alltags-Sprache',
        'hinweis' => 'Sie lesen die Seite in Leichter Sprache.',
    ],
];
