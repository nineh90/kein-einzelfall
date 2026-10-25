<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\KlartextSuche;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * „Klartext statt Fachsprache“ unter „Wissen“ (KEV-254).
 *
 * Taddis Dokument „0. Klartext statt Fachsprache“: Die Glossare sind ihre
 * Kategorien 1 bis 6, die 7 („Typische Sätze“) steckt seit KEV-262 in 3 bis
 * 6. Ihr Wunsch: „Vielleicht kann das wieder als Kacheln dargestellt werden,
 * aber gleichzeitig eine Suchmaske, die auf alle Kategorien zugreift?“
 *
 * - Übersicht mit ihrem Text, darunter die Suche über alle Kategorien
 *   (Baustein `glossar_suche`) und die sechs Kategorien als Kacheln (Baustein
 *   `unterseiten`, Bild und Unterzeile der jeweiligen Seite). Im Menü stehen
 *   die Kategorien jetzt unter diesem Punkt (config/navigation.php).
 * - Ihr Text in fünf statt sieben Absätzen: Ab sechs klappt der Textbaustein
 *   den Rest unter „Weiterlesen“ ein, das träfe hier den Gruß. Zusammengezogen
 *   sind „Genau deshalb …“ mit dem Satz danach und „Denn du bist ein Teil
 *   davon!“ mit dem Absatz davor. „du“ klein wie bei ihr.
 * - Ein Bild gibt es im Drive noch nicht, bis dahin der Platzhalter.
 * - Die Kategorie 1 heißt jetzt wie bei ihr „Abkürzungen im Sozialrecht“
 *   (vorher „Glossar“), Adresse /glossar bleibt.
 *
 * Legt nur an, was fehlt.
 */
class KlartextStattFachspracheSeeder extends Seeder
{
    public const SLUG = KlartextSuche::SLUG;

    public const TITEL = 'Klartext statt Fachsprache';

    public const ABSAETZE = [
        'Wenn andere über deine Gesundheit, deine Unterstützung, deine Leistungen oder sogar über wichtige Teile '
            .'deines Lebens entscheiden, kann das Angst machen. Besonders dann, wenn du einen Bescheid, ein '
            .'Gutachten oder einen medizinischen Bericht vor dir hast und kaum verstehst, was dort eigentlich über '
            .'dich geschrieben und entschieden wurde.',
        'Genau deshalb machen wir hier Fachsprache verständlich. Wir erklären Begriffe, typische Formulierungen '
            .'und ganze Sätze aus *Behörden & Bescheiden*, *Gericht & Verfahren*, *medizinischen Berichten & '
            .'Gutachten* sowie aus dem *Sozialrecht & Hilfesystem*.',
        '*Damit du besser verstehst, was über dich geschrieben wird, was eine Entscheidung für dich bedeutet und '
            .'du nicht das Gefühl haben musst, einem System ausgeliefert zu sein, dessen Sprache du nicht '
            .'verstehst.*',
        'Wenn dir hier ein Begriff, eine Formulierung oder ein Satz fehlt, schick uns gern eine Mail an '
            .'*kontakt@kein-einzelfall.de*. Nur so kann diese Sammlung weiter wachsen. *Denn du bist ein Teil '
            .'davon!*',
        '*Gemeinsam KE!N EINZELFALL*',
    ];

    public const META_DESCRIPTION = 'Klartext statt Fachsprache: Abkürzungen, Begriffe und typische Sätze aus '
        .'Bescheiden, Gerichtsverfahren, Gutachten und dem Sozialrecht verständlich erklärt, mit Suche über alle '
        .'Kategorien. Von KE!N EINZELFALL e.V.';

    public function run(): void
    {
        self::anlegen();
    }

    public static function anlegen(): void
    {
        $seite = Page::firstOrCreate(
            ['slug' => self::SLUG, 'locale' => 'de'],
            [
                'titel' => self::TITEL,
                'meta_title' => self::TITEL.' - Kein Einzelfall e.V.',
                'meta_description' => self::META_DESCRIPTION,
                'published_at' => now(),
            ],
        );

        if ($seite->wasRecentlyCreated) {
            $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['absaetze' => self::ABSAETZE]]);
            $seite->blocks()->create(['typ' => 'glossar_suche', 'position' => 1, 'data' => []]);
            $seite->blocks()->create(['typ' => 'unterseiten', 'position' => 2, 'data' => []]);
        }

        Titelbilder::platzhalterSetzen();
    }
}
