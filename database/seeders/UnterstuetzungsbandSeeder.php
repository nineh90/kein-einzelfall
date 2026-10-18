<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Das Band „Unterstützung“ (Spenden, Mitglied werden) auf ausgewählten
 * Unterseiten (KEV-120).
 *
 * Es stand bis dahin auf der Startseite, unter „Verein“ und „Mitglieder“.
 * Kurz darauf folgte „Jetzt spenden“, die Startseite rief also zweimal
 * hintereinander zum Spenden auf. Wunsch des Vereins: dort weg und dafür
 * vereinzelt auf Unterseiten.
 *
 * Ausgewählt sind Seiten über den Verein und seine Arbeit. Bewusst nicht auf
 * Hilfe-Seiten (Selbsthilfe, Wissen, Anfragen): Wer dort Hilfe sucht, soll
 * nicht um Geld gebeten werden.
 *
 * Jeweils ans Ende des Inhalts, nur auf den deutschen Seiten. Die englischen
 * baut der UebersetzungenSeeder aus ihnen.
 *
 * Aufgerufen von der Migration (bestehende Datenbanken) und am Ende des
 * AltseiteSeeders (frische): Verein und Team legt erst der an, also nach
 * den Migrationen. Läuft beliebig oft, ohne ein zweites Band anzuhängen.
 */
class UnterstuetzungsbandSeeder extends Seeder
{
    /*
     * Nicht /veranstaltungen: Dort stehen die Blöcke als Einleitung über dem
     * Kalender, das Band landete also mitten auf der Seite. Stattdessen die
     * Seite der Veranstaltungsreihe.
     */
    public const SEITEN = [
        'verein',
        'ueber-uns-vorstand-und-team',
        'taetigkeits-und-jahresberichte',
        'kein-einzelfall-im-dialog',
    ];

    public const BAND = [
        'eyebrow' => 'Unterstützung',
        'zitat' => 'Sei Du dabei, jede Unterstützung zählt, egal wie gering!',
        'ctas' => [
            ['label' => 'Spenden', 'url' => '/spenden', 'variant' => 'light'],
            ['label' => 'Mitglied werden', 'url' => '/mitgliedschaft', 'variant' => 'outline'],
        ],
    ];

    public function run(): void
    {
        $anzahl = self::anhaengen();

        $this->command?->info("Band „Unterstützung“ auf {$anzahl} Seiten angehängt.");
    }

    /** @return int wie viele Seiten das Band neu bekommen haben */
    public static function anhaengen(): int
    {
        $anzahl = 0;

        $seiten = Page::query()
            ->where('locale', Language::standardCode())
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->whereIn('slug', self::SEITEN)
            ->get();

        foreach ($seiten as $seite) {
            if ($seite->blocks()->where('typ', 'cta_band')->exists()) {
                continue;
            }

            $seite->blocks()->create([
                'typ' => 'cta_band',
                'position' => (int) $seite->blocks()->max('position') + 1,
                'data' => self::BAND,
            ]);
            $anzahl++;
        }

        return $anzahl;
    }
}
