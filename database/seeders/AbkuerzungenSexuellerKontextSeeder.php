<?php

namespace Database\Seeders;

/**
 * Glossar „Abkürzungen im sexuellen Kontext“ unter „Wissen“ (KEV-154).
 *
 * Aus Taddis Dokument „2. Abkürzungen sexueller Kontext“: ihre Einleitung und
 * 109 Abkürzungen aus Sexdienstleistung, Sexualität und BDSM
 * (data/glossar-sexueller-kontext.json). Im Menü zwischen „Glossar“ (ihre 1)
 * und „Behörden & Bescheide“ (ihre 3).
 *
 * - Bei ihr steht jede Zeile als „AO ⇥ Alles ohne: Szenebegriff für …“. Geteilt
 *   am ersten Doppelpunkt, sonst am ersten Semikolon: davor die Auflösung,
 *   danach die Erklärung (erster Buchstabe groß, sie steht in eigener Zeile).
 * - Doppelte Abkürzungen (AO, FA, GB, HH) und solche, die es im ersten
 *   Glossar schon gibt (BBB, FF, KK, KV), bekommen die Auflösung ins
 *   Sprungziel: /abkuerzungen-im-sexuellen-kontext#ao-alles-ohne.
 * - „KE!N EINZELFALL e. V.“ → „e.V.“ wie überall auf der Website.
 * - Über der Liste steht bei ihr „TRIGGERWARNUNG“ ohne Text. Die Warnung ist
 *   deshalb von uns, gebaut wie ihre vor „Raum für Ungesagtes“ (KEV-156), und
 *   steht vor der Seite (Feld `triggerwarnung`, App\Support\Bereichswarnung).
 * - Bild: ihr „3. Abkürzungen sexueller Kontext“ (Buchstabenwürfel) aus dem
 *   Drive-Ordner „3. Wissen“, rechts um Wand verlängert.
 *
 * Legt nur an, was fehlt (GlossarSeite).
 */
class AbkuerzungenSexuellerKontextSeeder extends GlossarSeite
{
    public const SLUG = 'abkuerzungen-im-sexuellen-kontext';

    public const TITEL = 'Abkürzungen im sexuellen Kontext';

    public const ABSAETZE = [
        'Gerade im Bereich Sexdienstleistungen, Sexualität und BDSM begegnen einem viele Abkürzungen, '
            .'Szenebegriffe und Bezeichnungen, deren Bedeutung nicht immer sofort verständlich ist.',
        'Hier haben wir zahlreiche Begriffe gesammelt und kurz erklärt – von Bezeichnungen für Dienstleistungen '
            .'und Praktiken bis hin zu medizinischen, rechtlichen und szenespezifischen Abkürzungen.',
        'Uns ist wichtig, dabei nicht zu bewerten, sondern verständlich zu machen, was gemeint ist. Denn Sprache '
            .'kann Orientierung geben – besonders dann, wenn man sich in einem Umfeld bewegt, dessen Begriffe '
            .'einem noch nicht vertraut sind.',
        'KE!N EINZELFALL e.V. unterstützt Menschen aus der Sexdienstleistungsbranche unabhängig davon, ob ihre '
            .'Tätigkeit freiwillig und selbstbestimmt ausgeübt wird oder ob Zwang, Ausbeutung, Gewalt oder '
            .'Abhängigkeiten eine Rolle spielen.',
    ];

    public const META_DESCRIPTION = 'Abkürzungen im sexuellen Kontext: Begriffe aus Sexdienstleistung, '
        .'Sexualität und BDSM, ohne Wertung erklärt. Von KE!N EINZELFALL e.V.';

    public const DATEI = 'glossar-sexueller-kontext.json';

    /** Von uns, nach Taddis Warnung vor „Raum für Ungesagtes“. */
    public const TRIGGERWARNUNG = [
        'Auf dieser Seite werden Abkürzungen und Begriffe aus Sexdienstleistung, Sexualität und BDSM erklärt. '
            .'Manche Erklärungen beschreiben sexuelle Praktiken sehr direkt.',
        'Einzelne Begriffe können an sexualisierte Gewalt, Ausbeutung oder andere belastende Erfahrungen '
            .'erinnern.',
        'Bitte achte auf dich und entscheide selbst, ob und wann du diese Inhalte lesen möchtest.',
    ];
}
