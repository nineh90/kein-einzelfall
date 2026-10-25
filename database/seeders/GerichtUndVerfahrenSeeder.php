<?php

namespace Database\Seeders;

/**
 * Glossar „Gericht & Verfahren“ unter „Wissen“ (KEV-259).
 *
 * Aus Taddis Dokument „4. Gericht und Verfahren“ (Ordner „4. Klartext statt
 * Fachsprache“): Einleitung (fett wie bei ihr, die Adresse wird zum
 * Mailto-Link), 87 Begriffe mit Erklärung (data/glossar-gericht-und-verfahren.json)
 * und darunter ihre 53 typischen Sätze (KEV-262).
 *
 * - Aus dem Ticket: die Unterzeile unter dem Titel („Damit juristische
 *   Begriffe verständlicher werden“, Titelbilder) und die grüne Überschrift
 *   über der Einleitung, beide ohne Punkt wie im Ticket.
 * - Ihre Einleitung hier ohne „Gemeinsam KE!N EINZELFALL“ und mit „Fehlt Dir
 *   hier ein Begriff?“ nicht fett; so steht es in ihrem Dokument.
 * - „Einfach erklärt:“ vor jeder Erklärung ist weggelassen wie in den anderen
 *   Glossaren.
 * - 21 Begriffe gibt es schon in anderen Glossaren, die meisten in „Behörden
 *   & Bescheide“ (Akte, Antrag, Frist, Widerspruch …). Ihr Sprungziel endet
 *   hier auf „-gericht“ (#frist-gericht).
 * - Bild: ihr „Gericht und Verfahren“ (Richterhammer und Akten) aus dem
 *   Drive-Ordner „3. Wissen“, rechts um Wand verlängert.
 *
 * Legt nur an, was fehlt (GlossarSeite).
 */
class GerichtUndVerfahrenSeeder extends GlossarSeite
{
    public const SLUG = 'gericht-und-verfahren';

    public const TITEL = 'Gericht & Verfahren';

    public const TEXT_TITEL = 'Begriffe aus Widerspruchs-, Klage- und Gerichtsverfahren für dich';

    public const ABSAETZE = [
        'Hier findest Du typische Begriffe aus Klage-, Gerichts- und Rechtsmittelverfahren – verständlich '
            .'erklärt und mit Schwerpunkt auf Verfahren vor den Sozialgerichten.',
        'Die Erklärungen sollen Dir helfen, Schreiben und Abläufe besser einzuordnen. Sie ersetzen keine '
            .'individuelle Rechtsberatung.',
        'Fehlt Dir hier ein Begriff? Dann schreib uns gern an *kontakt@kein-einzelfall.de*. Wir ergänzen die '
            .'Liste und lassen sie gemeinsam mit euch weiter wachsen.',
    ];

    public const META_DESCRIPTION = 'Gericht & Verfahren: Begriffe aus Widerspruchs-, Klage- und '
        .'Gerichtsverfahren vor den Sozialgerichten, einfach erklärt. Von KE!N EINZELFALL e.V.';

    public const DATEI = 'glossar-gericht-und-verfahren.json';

    public const SAETZE_AUS = 'Klage-, Gerichts- und Rechtsmittelverfahren';
}
