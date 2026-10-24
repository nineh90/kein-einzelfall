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
        'kein-einzelfall-im-dialog',
    ];

    /**
     * Seiten mit dem Spendenkasten der Startseite („Jetzt spenden“, kompakt)
     * statt des Bands (KEV-219, Taddis Wunsch).
     */
    public const SPENDEN_STATT_BAND = [
        'taetigkeits-und-jahresberichte',
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

        self::spendenStattBand();

        return $anzahl;
    }

    /**
     * KEV-219: Auf den Seiten in SPENDEN_STATT_BAND steht statt des Bands der
     * Spendenkasten der Startseite, mit denselben Daten wie dort (so, wie der
     * Verein ihn gerade gepflegt hat). Ein Band, das der Verein geändert hat,
     * bleibt; ein Spendenkasten, der schon dasteht, auch.
     */
    public static function spendenStattBand(): void
    {
        $vorlage = Page::where('slug', 'startseite')->where('locale', Language::standardCode())->first()
            ?->blocks()->where('typ', 'donation_options')->value('data')
            ?? StartseiteSeeder::spendenBaustein();

        $seiten = Page::query()
            ->where('locale', Language::standardCode())
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->whereIn('slug', self::SPENDEN_STATT_BAND)
            ->get();

        foreach ($seiten as $seite) {
            if ($seite->blocks()->where('typ', 'donation_options')->exists()) {
                continue;
            }

            $band = $seite->blocks()->where('typ', 'cta_band')->get()->first(fn ($b) => $b->data == self::BAND);
            $position = $band?->position ?? (int) $seite->blocks()->max('position') + 1;
            $band?->delete();

            if ($seite->blocks()->where('typ', 'cta_band')->exists()) {
                continue; // vom Verein geändertes Band: nicht anfassen
            }

            $seite->blocks()->create([
                'typ' => 'donation_options',
                'position' => $position,
                'data' => is_array($vorlage) ? $vorlage : json_decode($vorlage, true),
            ]);
        }
    }
}
