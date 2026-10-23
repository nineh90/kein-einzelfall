<?php

namespace Database\Seeders;

/**
 * Zweites Glossar „Behörden & Bescheide“ unter „Wissen“ (KEV-155).
 *
 * Aus Taddis Dokument „3. Behörden und Bescheide“: die Einleitung (fett wie
 * bei ihr, die Adresse wird zum Mailto-Link) und 128 Begriffe mit ihrer
 * Erklärung (data/glossar-behoerden.json). Anders als im ersten Glossar sind
 * das keine Abkürzungen, sondern Wörter aus Bescheiden, jedes erklärt.
 *
 * - Die Seite trägt Einleitung und den Baustein „Glossar“; die Einträge sind
 *   ihr zugeordnet (`seite_gruppe`). So pflegt der Verein beides wie beim
 *   ersten Glossar im Panel.
 * - Bei Taddi beginnt jede Erklärung mit „Einfach erklärt:“. Das steht 128-mal
 *   gleich da und ist weggelassen; die Einleitung sagt es einmal.
 * - Ihre Reihenfolge war fast alphabetisch, das Glossar sortiert selbst.
 * - Ein eigenes Bild gibt es noch nicht, bis dahin der Platzhalter.
 *
 * Legt nur an, was fehlt (GlossarSeite).
 */
class BehoerdenUndBescheideSeeder extends GlossarSeite
{
    public const SLUG = 'behoerden-und-bescheide';

    public const TITEL = 'Behörden & Bescheide';

    public const ABSAETZE = [
        'Hier findest du typische Begriffe aus Bescheiden und Behördenschreiben – von *Anhörung* über '
            .'*Mitwirkungspflicht* bis *Widerspruchsbescheid*.',
        'Kurz erklärt, verständlich formuliert und so aufgebaut, dass du schnell nachschauen kannst, was ein '
            .'Begriff bedeutet.',
        '*Fehlt dir hier etwas?* Dann schick uns gern eine Mail an *kontakt@kein-einzelfall.de*. Wir ergänzen '
            .'die Liste und lassen sie gemeinsam mit euch weiter wachsen.',
        '*Gemeinsam KE!N EINZELFALL*',
    ];

    public const META_DESCRIPTION = 'Behörden & Bescheide: Begriffe aus Bescheiden und Behördenschreiben, von '
        .'Anhörung bis Widerspruchsbescheid, einfach erklärt. Von KE!N EINZELFALL e.V.';

    public const DATEI = 'glossar-behoerden.json';
}
