<?php

/*
 * Was diese Website im Browser der Besucherin ablegt — vollständig, an einer
 * Stelle.
 *
 * ── Warum es diese Datei gibt ───────────────────────────────────────────────
 *
 * Bis zur Trigger-Warnung gab es genau einen gespeicherten Wert, und die
 * Darstellungs-Toolbar hatte dafür ihren eigenen Knopf „Alles zurücksetzen“.
 * Mit dem zweiten Wert war diese Beschriftung stillschweigend falsch geworden:
 * Sie räumte weiterhin nur ihren eigenen Schlüssel ab.
 *
 * Genau so entsteht der Zustand, den niemand mehr überblickt. Deshalb steht
 * hier ab jetzt jeder Schlüssel, und drei Stellen lesen daraus:
 *
 *   - die Übersicht auf /barrierefreiheit (Baustein `speicher_uebersicht`)
 *   - der Knopf „Alles zurücksetzen“ in der Darstellungs-Toolbar
 *   - die Datenschutzerklärung — sie muss diese Liste nennen
 *
 * ⚠️ Wer künftig etwas im Browser speichert, trägt es hier ein. Sonst lässt es
 * sich nicht zurücksetzen, und die Datenschutzerklärung wird unvollständig.
 *
 * ── Rechtlich ───────────────────────────────────────────────────────────────
 *
 * Alles hier ist eine Einstellung auf ausdrücklichen Wunsch der lesenden Person
 * und damit einwilligungsfrei (§ 25 Abs. 2 Nr. 2 TDDDG): technisch notwendig,
 * um einen ausdrücklich angeforderten Dienst zu erbringen. Kein Tracking, keine
 * Kennung, nichts geht an den Server. Erwähnt werden muss es trotzdem.
 */

// Der Name des Darstellungs-Schlüssels steht in dessen eigener Konfiguration.
// Ihn hier abzuschreiben hiesse, dass ein Umbenennen dort diese Liste still
// falsch macht — und „Alles zurücksetzen“ dann einen Wert stehen liesse.
$darstellung = require __DIR__.'/darstellung.php';

return [

    'eintraege' => [

        [
            'schluessel' => 'darstellung',
            'label' => 'rahmen.speicher.darstellung.label',
            'text' => 'rahmen.speicher.darstellung.text',
            'local' => [$darstellung['speicher']],
            'session' => [],
        ],

        [
            'schluessel' => 'trigger',
            'label' => 'rahmen.speicher.trigger.label',
            'text' => 'rahmen.speicher.trigger.text',
            'local' => ['ke.trigger.aus'],
            'session' => ['ke.trigger.gesehen'],
        ],

    ],
];
