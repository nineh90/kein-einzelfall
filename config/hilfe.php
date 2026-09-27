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
 *
 * Zuletzt an den Seiten der Träger geprüft am 27.09.2026 (KEV-46):
 * hilfetelefon.de, maennerhilfetelefon.de, telefonseelsorge.de,
 * weisser-ring.de.
 */
return [

    /*
     * Alle Nummern, für die ausführliche Hilfe-Box (Fehlerseite, Bausteine
     * ohne „kompakt“). Reihenfolge = Priorität für die Zielgruppe: erst der
     * auf Opfer spezialisierte Dienst, dann die allgemeinen.
     *
     * „angaben“ steht unter dem Namen, mit „·“ verbunden.
     */
    'nummern' => [
        'weisser-ring' => [
            'name' => 'Opfer-Telefon WEISSER RING',
            'nummer' => '116 006',
            'tel' => '+49116006',
            'angaben' => ['Für Betroffene von Straftaten', 'täglich 7–22 Uhr', 'kostenfrei'],
        ],
        'telefonseelsorge' => [
            'name' => 'TelefonSeelsorge',
            'nummer' => '116 123',
            'tel' => '+49116123',
            'angaben' => ['Rund um die Uhr', 'kostenfrei', 'anonym'],
        ],
        'frauen' => [
            'name' => 'Hilfetelefon Gewalt gegen Frauen',
            'nummer' => '116 016',
            'tel' => '+49116016',
            'angaben' => ['Rund um die Uhr', 'kostenfrei', 'anonym', 'mehrsprachig'],
        ],
        'maenner' => [
            // Offizieller Name „an Männern“. Anders als die übrigen nicht rund
            // um die Uhr erreichbar, deshalb stehen die Zeiten dabei.
            'name' => 'Hilfetelefon Gewalt an Männern',
            'nummer' => '0800 123 99 00',
            'tel' => '+498001239900',
            'angaben' => ['Beratung für Männer, die Gewalt erlebt haben', 'Mo–Do 8–20 Uhr, Fr 8–15 Uhr', 'kostenfrei'],
        ],
        'missbrauch' => [
            'name' => 'Hilfetelefon Sexueller Missbrauch',
            'nummer' => '0800 22 55 530',
            'tel' => '+498002255530',
            'angaben' => ['Mo, Mi, Fr 9–14 Uhr, Di, Do 15–20 Uhr', 'kostenfrei', 'anonym'],
        ],
        'kummer' => [
            'name' => 'Nummer gegen Kummer (für Kinder und Jugendliche)',
            'nummer' => '116 111',
            'tel' => '+49116111',
            'angaben' => ['Mo–Sa 14–20 Uhr', 'kostenfrei', 'anonym'],
        ],
    ],

    /*
     * Die Kurzfassung (Startseite): zwei Spalten, so vom Verein gewünscht
     * (Taddi, KEV-46). Links die Hilfetelefone bei Gewalt, rechts die
     * allgemeinen Stellen.
     */
    'kompakt' => [
        'links' => ['frauen', 'maenner'],
        'rechts' => ['telefonseelsorge', 'weisser-ring'],
    ],

    /*
     * Die Notfall-Zeile in der Fußzeile jeder Seite (KEV-42). Das Hinweisfenster
     * verspricht „Die Notfallnummern stehen am Ende jeder Seite“; vorher
     * standen sie nur auf der Startseite. Zwei Nummern plus der Polizei-Notruf,
     * mehr wird in einer Zeile unübersichtlich. Beide rund um die Uhr oder fast.
     */
    'fusszeile' => ['weisser-ring', 'telefonseelsorge'],

    /* Bei unmittelbarer Gefahr — steht separat und optisch abgesetzt. */
    'notruf' => [
        ['name' => 'Polizei-Notruf', 'nummer' => '110', 'tel' => '110'],
        ['name' => 'Rettungsdienst/Feuerwehr', 'nummer' => '112', 'tel' => '112'],
    ],
];
