<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Die Seite „Vorstand und Team“ nach Taddis KEV-294: Einleitung links, „Auf
 * dieser Seite“ rechts daneben mit Vorstand, Team und Herr und Frau
 * Unbekannt, neue Überschrift und ein Schlusssatz.
 *
 * Aufgerufen von der Migration (bestehende Datenbanken) und am Ende des
 * AltseiteSeeders (frische): Die Seite legt erst der TeamUndGruppenSeeder
 * an. Ändert nur, was noch so dasteht wie vorher. Hat der Verein im Panel
 * schon etwas geändert, bleibt es.
 */
class TeamseiteSeeder extends Seeder
{
    public const SLUG = 'ueber-uns-vorstand-und-team';

    /** Die Überschrift der Einleitung bis KEV-294 (KEV-64). */
    public const ALTER_TITEL = 'Unser Team – mit Herz, Haltung und Vision';

    /** KEV-294, Taddi: „WIR“ groß, wie sie es geschrieben hat. */
    public const TITEL = 'WIR – mit Herz, Haltung und Vision';

    /** KEV-294: „ganz unten muss noch ein Satz dazu“, unter die Einleitung. */
    public const SCHLUSS = 'Doch ohne das Team wäre all das nicht möglich. Gemeinsam mit engagierten Mitgliedern '
        .'und Aktivisten, die ihre Erfahrungen, Fähigkeiten und Ideen einbringen, bewegen wir viel und schaffen '
        .'Veränderungen.';

    /** KEV-294: im Verzeichnis „Vorstand“, die Überschrift bleibt „Vorstandsebene“. */
    public const VORSTAND_IM_VERZEICHNIS = 'Vorstand';

    public function run(): void
    {
        self::fuellen();
    }

    public static function fuellen(): void
    {
        $seite = Page::query()
            ->where('slug', self::SLUG)
            ->where('locale', Language::standardCode())
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->first();

        if (! $seite) {
            return;
        }

        $einleitung = $seite->blocks()->where('typ', 'text')->get()
            ->first(fn ($b) => ($b->data['titel'] ?? null) === self::ALTER_TITEL);

        if ($einleitung) {
            $absaetze = $einleitung->data['absaetze'] ?? [];

            if (! in_array(self::SCHLUSS, $absaetze, true)) {
                $absaetze[] = self::SCHLUSS;
            }

            $einleitung->update(['data' => array_replace($einleitung->data, [
                'titel' => self::TITEL,
                'absaetze' => $absaetze,
                'verzeichnis_daneben' => true,
            ])]);
        }

        $seite->blocks()->where('typ', 'team_grid')->get()
            ->filter(fn ($b) => ($b->data['bereich'] ?? null) === TeamUndGruppenSeeder::BEREICH_VORSTAND
                && blank($b->data['verzeichnis_titel'] ?? null))
            ->each(fn ($b) => $b->update(['data' => array_replace($b->data, [
                'verzeichnis_titel' => self::VORSTAND_IM_VERZEICHNIS,
            ])]));
    }
}
