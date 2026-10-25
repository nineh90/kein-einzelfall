<?php

namespace Database\Seeders;

/**
 * Glossar „Sozialrecht & Hilfesystem“ unter „Wissen“ (KEV-261).
 *
 * Aus Taddis Dokument „6. Sozialrecht und Hilfesysteme“ (Ordner „4. Klartext
 * statt Fachsprache“): Einleitung (fett wie bei ihr, die Adresse wird zum
 * Mailto-Link), 99 Begriffe mit Erklärung (data/glossar-sozialrecht-hilfesystem.json)
 * und darunter ihre 61 typischen Sätze (KEV-262).
 *
 * - Aus dem Ticket: die Unterzeile unter dem Titel („Leistungen verstehen.
 *   Zuständigkeiten einordnen. Unterstützung finden.“, Titelbilder) und die
 *   grüne Überschrift über der Einleitung.
 * - „Einfach erklärt:“ vor jeder Erklärung ist weggelassen wie bei
 *   „Behörden & Bescheide“.
 * - Abkürzungen wie „GdB – Grad der Behinderung“ bleiben so im Begriff, wie
 *   Taddi sie schreibt; die Suche findet sie trotzdem unter „GdB“.
 * - Sieben Begriffe gibt es schon in anderen Glossaren (Antrag, Anspruch,
 *   Auskunft, Bedarf, Persönliches Budget, Pflegegrad, Zuständigkeit). Ihr
 *   Sprungziel endet hier auf „-sozialrecht“ (#antrag-sozialrecht).
 * - Bild: ihr „Sozialrecht & Hilfesystem“ (Kasten mit Registern) aus dem
 *   Drive-Ordner „3. Wissen“, rechts um Wand verlängert.
 *
 * Legt nur an, was fehlt (GlossarSeite).
 */
class SozialrechtUndHilfesystemSeeder extends GlossarSeite
{
    public const SLUG = 'sozialrecht-und-hilfesystem';

    public const TITEL = 'Sozialrecht & Hilfesystem';

    public const TEXT_TITEL = 'Zwischen Leistungsträgern, Teilhabe und Pflege den Überblick behalten.';

    public const ABSAETZE = [
        'Hier findest Du typische Begriffe aus dem Sozialrecht und aus verschiedenen Hilfesystemen – von '
            .'*Leistungsträger* über *Teilhabe und Eingliederungshilfe* bis zu *GdB, GdS und Pflegegrad*.',
        'Kurz erklärt, verständlich formuliert und so aufgebaut, dass Du schnell nachschauen kannst, was ein '
            .'Begriff bedeutet.',
        'Die Erklärungen sollen Dir helfen, Begriffe, Leistungen und Zuständigkeiten besser einzuordnen. Sie '
            .'ersetzen keine individuelle Rechtsberatung oder Prüfung eines konkreten Leistungsanspruchs.',
        '*Fehlt Dir hier etwas?* Dann schick uns gern eine Mail an *kontakt@kein-einzelfall.de*. Wir ergänzen '
            .'die Liste und lassen sie gemeinsam mit euch weiter wachsen.',
        '*Gemeinsam KE!N EINZELFALL*',
    ];

    public const META_DESCRIPTION = 'Sozialrecht & Hilfesystem: Begriffe von Leistungsträger über Teilhabe '
        .'und Eingliederungshilfe bis GdB, GdS und Pflegegrad, einfach erklärt. Von KE!N EINZELFALL e.V.';

    public const DATEI = 'glossar-sozialrecht-hilfesystem.json';

    public const SAETZE_AUS = 'dem Sozialrecht und aus verschiedenen Hilfesystemen';
}
