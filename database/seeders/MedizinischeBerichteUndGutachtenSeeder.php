<?php

namespace Database\Seeders;

/**
 * Glossar „Medizinische Berichte & Gutachten“ unter „Wissen“ (KEV-260).
 *
 * Aus Taddis Dokument „5. Medizinische Berichte und Gutachten“ (Ordner
 * „4. Klartext statt Fachsprache“): Einleitung (fett wie bei ihr, die Adresse
 * wird zum Mailto-Link), 115 Begriffe mit Erklärung
 * (data/glossar-medizinische-berichte.json) und darunter ihre 60 typischen
 * Sätze (KEV-262).
 *
 * - Aus dem Ticket: die Unterzeile unter dem Titel („Befunde verstehen.
 *   Gutachten besser einordnen.“, Titelbilder) und die grüne Überschrift über
 *   der Einleitung („Was medizinische Berichte wirklich sagen“, ohne Punkt wie
 *   im Ticket).
 * - „Einfach erklärt:“ vor jeder Erklärung ist weggelassen wie in den anderen
 *   Glossaren. „Narkoseprotokoll“ stand bei ihr ganz am Ende, das Glossar
 *   sortiert selbst.
 * - Sechs Begriffe gibt es schon in anderen Glossaren (Erwerbsfähigkeit,
 *   Erwerbsminderung, Medizinischer Dienst, Rehabilitationsbedarf,
 *   Stellungnahme, Teilhabe). Ihr Sprungziel endet hier auf „-medizin“.
 * - Bild: ihr „Medizinische Berichte & Gutachten“ (Mappe mit Stethoskop) aus
 *   dem Drive-Ordner „3. Wissen“, rechts um Wand verlängert.
 *
 * Legt nur an, was fehlt (GlossarSeite).
 */
class MedizinischeBerichteUndGutachtenSeeder extends GlossarSeite
{
    public const SLUG = 'medizinische-berichte-und-gutachten';

    public const TITEL = 'Medizinische Berichte & Gutachten';

    public const TEXT_TITEL = 'Was medizinische Berichte wirklich sagen';

    public const ABSAETZE = [
        'Hier findest Du typische Begriffe aus *Arztbriefen, Befundberichten, medizinischen Gutachten, '
            .'Reha-Berichten und sozialmedizinischen Einschätzungen*.',
        'Kurz erklärt, verständlich formuliert und so aufgebaut, dass Du schnell nachschauen kannst, was ein '
            .'Begriff bedeutet.',
        'Die Erklärungen helfen Dir dabei, medizinische Formulierungen besser zu verstehen und einzuordnen. Sie '
            .'ersetzen keine ärztliche Bewertung eines konkreten Befundes und keine individuelle Rechtsberatung.',
        '*Fehlt Dir hier etwas?* Dann schick uns gern eine Mail an *kontakt@kein-einzelfall.de*. Wir ergänzen '
            .'die Liste und lassen sie gemeinsam mit euch weiter wachsen.',
        '*Gemeinsam KE!N EINZELFALL*',
    ];

    public const META_DESCRIPTION = 'Medizinische Berichte & Gutachten: Begriffe aus Arztbriefen, '
        .'Befundberichten, Gutachten und Reha-Berichten, einfach erklärt. Von KE!N EINZELFALL e.V.';

    public const DATEI = 'glossar-medizinische-berichte.json';

    public const SAETZE_AUS = 'Arztbriefen, Befundberichten und Gutachten';
}
