<?php

/*
 * Notfall- und Hilfenummern.
 *
 * Bewusst als Config und nicht im Seitentext: diese Nummern müssen an jeder
 * Stelle identisch und aktuell sein. Eine falsche Nummer auf einer Unterseite
 * wäre hier ein echter Schaden.
 *
 * Vor Go-Live vom Verein gegenprüfen lassen — Zuständigkeiten und Nummern
 * ändern sich, und der Verein kennt die Landschaft besser als wir.
 */
return [

    /*
     * Immer sichtbar in der Hilfe-Box. Reihenfolge = Priorität für die Zielgruppe:
     * erst der auf Opfer spezialisierte Dienst, dann die allgemeinen.
     */
    'nummern' => [
        [
            'name' => 'Opfer-Telefon WEISSER RING',
            'nummer' => '116 006',
            'tel' => '+49116006',
            'zeiten' => 'täglich 7–22 Uhr',
            'hinweis' => 'kostenlos, auch anonym',
        ],
        [
            'name' => 'Telefonseelsorge',
            'nummer' => '0800 111 0 111',
            'tel' => '+498001110111',
            'zeiten' => 'rund um die Uhr',
            'hinweis' => 'kostenlos, anonym, vertraulich',
        ],
        [
            'name' => 'Hilfetelefon Gewalt gegen Frauen',
            'nummer' => '116 016',
            'tel' => '+49116016',
            'zeiten' => 'rund um die Uhr',
            'hinweis' => 'kostenlos, in 18 Sprachen',
        ],
        [
            'name' => 'Hilfetelefon Sexueller Missbrauch',
            'nummer' => '0800 22 55 530',
            'tel' => '+498002255530',
            'zeiten' => 'Mo, Mi, Fr 9–14 Uhr · Di, Do 15–20 Uhr',
            'hinweis' => 'kostenlos und anonym',
        ],
        [
            'name' => 'Nummer gegen Kummer (für Kinder und Jugendliche)',
            'nummer' => '116 111',
            'tel' => '+49116111',
            'zeiten' => 'Mo–Sa 14–20 Uhr',
            'hinweis' => 'kostenlos und anonym',
        ],
    ],

    /* Bei unmittelbarer Gefahr — steht separat und optisch abgesetzt. */
    'notruf' => [
        'name' => 'Polizei-Notruf',
        'nummer' => '110',
        'tel' => '110',
    ],
];
