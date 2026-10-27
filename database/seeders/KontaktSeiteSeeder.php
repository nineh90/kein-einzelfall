<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Die Kontaktseite nach Taddis Tickets (KEV-267 bis KEV-272).
 *
 * Text aus ihrem Dokument „Aufbau Kontakt“ (Drive, „7. Kontakt & Austausch“).
 * Fett wie dort: der Vereinsname, „Und wenn du nicht weißt …“ und der
 * Schlusssatz. „Aufbau Kontakt / Kontaktmöglichkeit / Landesstellen /
 * Anfragen & Austausch“ darüber ist ihre Gliederung des Bereichs, kein Text.
 *
 * Tauscht jeweils nur, wo noch der Wortlaut der Altseite steht. Was der
 * Verein im Panel geändert hat, bleibt.
 */
class KontaktSeiteSeeder extends Seeder
{
    public const SLUG = 'kontakt';

    /** KEV-271: ohne „des KE!N EINZELFALL e.V.“, wie im Verzeichnis „Auf dieser Seite“. */
    public const TITEL_ALT = 'Kontaktmöglichkeiten des KE!N EINZELFALL e.V.';

    public const TITEL = 'Kontaktmöglichkeiten';

    /** Erster Abschnitt der Altseite: Unterzeile und Anschrift (KEV-272). */
    public const EINLEITUNG_ALT = [
        'Alle Ansprechpartner, Landesstellen und Zuständigkeiten auf einen Blick',
        'KE!N EINZELFALL e.V.',
        'Schiffbeker Höhe 30',
        '22119 Hamburg',
    ];

    /** KEV-272. Die Anschrift steht weiter unten unter „Kontakt & Geschäftssitz“. */
    public const EINLEITUNG = [
        'Bei *KE!N EINZELFALL e.V.* möchten wir dir diesen Weg so einfach wie möglich machen. Deshalb findest du '
            .'hier eine Übersicht unserer Vorstandsebene, Landesstellen und der Sachgebietsaufteilung mit den '
            .'jeweiligen Ansprechpersonen und Kontaktmöglichkeiten.',
        'Ganz gleich, ob du Unterstützung suchst, Fragen hast, dich engagieren möchtest oder mit uns '
            .'zusammenarbeiten willst – hier findest du den passenden Kontakt.',
        '*Und wenn du nicht weißt, an wen du dich wenden sollst, ist das auch kein Problem.* Melde dich einfach '
            .'bei uns unter kontakt@kein-einzelfall.de. Wir helfen dir, die richtige Ansprechperson zu finden.',
        '*Du bist bei uns willkommen – mit allem, was dich bewegt.*',
    ];

    /** KEV-267: Die Kassenwartin ist unter finanzen@ erreichbar. */
    public const KASSENWARTIN_ALT = 'Kassenwartin: Petra Hildebrandt, hildebrandt@kein-einzelfall.de';

    public const KASSENWARTIN = 'Kassenwartin: Petra Hildebrandt, finanzen@kein-einzelfall.de';

    /** KEV-270: Taddis Reihenfolge, Anfragen zuerst. */
    public const SACHGEBIETE = [
        'Anfragen: kontakt@kein-einzelfall.de',
        'Verwaltung: verwaltung@kein-einzelfall.de',
        'Finanzen: finanzen@kein-einzelfall.de',
        'Selbsthilfegruppen: selbsthilfe@kein-einzelfall.de',
        'Arbeitsgruppen: arbeitsgruppe@kein-einzelfall.de',
        'Veranstaltungen: veranstaltung@kein-einzelfall.de',
        'Akten & Dokumente: dokumente@kein-einzelfall.de',
        'Presse & Öffentlichkeitsarbeit: pressekontakt@kein-einzelfall.de',
        'Datenschutz: datenschutz@kein-einzelfall.de',
        'Beschwerdemanagement: beschwerdemanagement@kein-einzelfall.de',
        'Opferbeauftragter: opferbeauftragter@kein-einzelfall.de',
    ];

    public function run(): void
    {
        self::aktualisieren();
    }

    public static function aktualisieren(): void
    {
        $seite = Page::query()
            ->where('slug', self::SLUG)
            ->where('locale', Language::standardCode())
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->first();

        foreach ($seite?->blocks()->where('typ', 'text')->get() ?? [] as $block) {
            $data = $block->data ?? [];
            $absaetze = $data['absaetze'] ?? [];

            if (($data['titel'] ?? null) === self::TITEL_ALT) {
                $data['titel'] = self::TITEL;

                if ($absaetze === self::EINLEITUNG_ALT) {
                    $data['absaetze'] = self::EINLEITUNG;
                }
            }

            if (($data['titel'] ?? null) === 'Vorstandsebene') {
                $data['absaetze'] = array_map(
                    fn ($a) => $a === self::KASSENWARTIN_ALT ? self::KASSENWARTIN : $a,
                    $absaetze,
                );
            }

            // Dieselben Zeilen in anderer Reihenfolge: nur dann umsortieren.
            if (($data['titel'] ?? null) === 'Sachgebietsaufteilung' && is_array($absaetze)
                && count($absaetze) === count(self::SACHGEBIETE)
                && array_diff($absaetze, self::SACHGEBIETE) === []) {
                $data['absaetze'] = self::SACHGEBIETE;
            }

            if ($data !== $block->data) {
                $block->update(['data' => $data]);
            }
        }
    }
}
