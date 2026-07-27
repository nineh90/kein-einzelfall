<?php

/*
 * Die Darstellungs-Einstellungen der Barrierefreiheits-Toolbar.
 *
 * Bewusst hier und nicht in JavaScript: Das Panel wird serverseitig gerendert.
 * Damit stehen alle Beschriftungen im ausgelieferten HTML — die Toolbar ist
 * dadurch selbst dann noch vorhanden und vorlesbar, wenn das Bundle nicht lädt.
 * Vorher entstand das Panel per JavaScript aus einer Liste im Bundle; ein
 * Screenreader fand dort vor dem Ausführen des Skripts schlicht nichts.
 *
 * Zweiter Grund: Die Zuordnung „Wert -> Wirkung auf <html>" brauchen zwei
 * Stellen — das Inline-Skript im <head> (das die gespeicherten Einstellungen
 * anwendet, bevor der Browser zeichnet) und die Bedienung im Bundle. Beide
 * lesen jetzt aus 'anwendung' unten. Vorher stand die Tabelle doppelt da und
 * konnte auseinanderlaufen.
 */

return [

    /*
     * Reihenfolge und Beschriftung im Panel.
     *
     * typ:
     *   stufen   – mehrere Stufen, Wert ist eine Zahl (Index in 'anwendung')
     *   auswahl  – sich ausschliessende Möglichkeiten, Wert ist eine Zeichenkette
     *   schalter – an/aus
     */
    'optionen' => [

        'schrift' => [
            'label' => 'Schriftgröße',
            'typ' => 'stufen',
            'werte' => [
                ['wert' => 0, 'label' => 'Standard'],
                ['wert' => 1, 'label' => 'Groß'],
                ['wert' => 2, 'label' => 'Größer'],
                ['wert' => 3, 'label' => 'Sehr groß'],
            ],
        ],

        'zeilen' => [
            'label' => 'Zeilenabstand',
            'typ' => 'stufen',
            'werte' => [
                ['wert' => 0, 'label' => 'Standard'],
                ['wert' => 1, 'label' => 'Weit'],
                ['wert' => 2, 'label' => 'Sehr weit'],
            ],
        ],

        'zeichen' => [
            'label' => 'Buchstabenabstand',
            'typ' => 'stufen',
            'werte' => [
                ['wert' => 0, 'label' => 'Standard'],
                ['wert' => 1, 'label' => 'Weit'],
                ['wert' => 2, 'label' => 'Sehr weit'],
            ],
        ],

        'kontrast' => [
            'label' => 'Kontrast & Farben',
            'typ' => 'auswahl',
            'werte' => [
                ['wert' => '', 'label' => 'Standard'],
                ['wert' => 'dunkel', 'label' => 'Dunkel'],
                ['wert' => 'hoch', 'label' => 'Hoher Kontrast'],
                ['wert' => 'monochrom', 'label' => 'Graustufen'],
            ],
        ],

        'lesbar' => ['label' => 'Gut lesbare Schrift', 'typ' => 'schalter'],
        'dyslexie' => ['label' => 'Schrift für Legasthenie', 'typ' => 'schalter'],
        'leselinie' => ['label' => 'Leselinie', 'typ' => 'schalter'],
        'links' => ['label' => 'Links hervorheben', 'typ' => 'schalter'],
        'cursor' => ['label' => 'Großer Mauszeiger', 'typ' => 'schalter'],
        'ruhe' => ['label' => 'Bewegung stoppen', 'typ' => 'schalter'],
        'bilder' => ['label' => 'Bilder ausblenden', 'typ' => 'schalter'],
    ],

    /*
     * Wie ein gespeicherter Wert auf <html> landet.
     *
     * Angefasst wird ausschliesslich das <html>-Element — nie einzelne Bausteine.
     * Dadurch wirkt jede Einstellung automatisch auch auf Komponenten, die es
     * heute noch gar nicht gibt.
     */
    'anwendung' => [

        // Wert ist ein Index in die jeweilige Liste.
        'variablen' => [
            'schrift' => ['--a11y-font-scale', [1, 1.15, 1.3, 1.5]],
            'zeilen' => ['--a11y-line-height', [1.7, 2, 2.3]],
            'zeichen' => ['--a11y-letter-spacing', ['0em', '0.05em', '0.1em']],
        ],

        // Wert landet unverändert als <html data-…="…">.
        'datensaetze' => ['kontrast'],

        // An/aus wird zu <html class="a11y-…">.
        'klassen' => ['lesbar', 'dyslexie', 'leselinie', 'links', 'cursor', 'ruhe', 'bilder'],
    ],

    // Schlüssel im localStorage.
    'speicher' => 'ke-a11y',
];
