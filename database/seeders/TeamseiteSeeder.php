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
 * KEV-295: Titel mit „&“, keine Zwischentexte und Symbole mehr bei Team und
 * Herr und Frau Unbekannt. Die Seite ist nur noch Einleitung, Vorstandsebene
 * und Team, Herr und Frau Unbekannt mittig unter dem Team.
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

    /** Der Seitentitel bis KEV-295 (KEV-179). */
    public const ALTER_SEITENTITEL = 'Über uns – Vorstand und Team';

    /** KEV-295: „&“ wie bei „Gruppen & Veranstaltungen“. */
    public const SEITENTITEL = 'Über uns – Vorstand & Team';

    /** KEV-294: im Verzeichnis „Vorstand“, die Überschrift bleibt „Vorstandsebene“. */
    public const VORSTAND_IM_VERZEICHNIS = 'Vorstand';

    public function run(): void
    {
        self::fuellen();
        self::teamZusammenfassen();
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

    /**
     * KEV-295: Titel mit „&“; die Texte vor dem Team und vor Herr und Frau
     * Unbekannt mitsamt ihren Symbolen weg; das Team bekommt die Überschrift
     * „Team“ wie der Vorstand „Vorstandsebene“, Herr und Frau Unbekannt
     * stehen im selben Raster mittig darunter.
     */
    public static function teamZusammenfassen(): void
    {
        $seite = Page::query()
            ->where('slug', self::SLUG)
            ->where('locale', Language::standardCode())
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->first();

        if (! $seite) {
            return;
        }

        if ($seite->titel === self::ALTER_SEITENTITEL) {
            $seite->update([
                'titel' => self::SEITENTITEL,
                'meta_title' => $seite->meta_title === self::ALTER_SEITENTITEL.' - Kein Einzelfall e.V.'
                    ? self::SEITENTITEL.' - Kein Einzelfall e.V.'
                    : $seite->meta_title,
            ]);
        }

        $bloecke = $seite->blocks()->orderBy('position')->get();
        $team = $bloecke->first(fn ($b) => $b->typ === 'team_grid'
            && ($b->data['bereich'] ?? null) === TeamUndGruppenSeeder::BEREICH_TEAM);

        // Ohne das Team-Raster gäbe es nichts, worunter die anderen rücken.
        if (! $team) {
            return;
        }

        $bloecke->filter(fn ($b) => $b->typ === 'text' && in_array($b->data['titel'] ?? null,
            ['Team', TeamUndGruppenSeeder::HINTERGRUND_UEBERSCHRIFT], true))
            ->each->delete();

        $bloecke->filter(fn ($b) => $b->typ === 'team_grid'
            && ($b->data['bereich'] ?? null) === TeamUndGruppenSeeder::BEREICH_HINTERGRUND)
            ->each->delete();

        $team->update(['data' => array_replace($team->data, [
            'titel' => ($team->data['titel'] ?? null) ?: 'Team',
            'anhaengen' => TeamUndGruppenSeeder::BEREICH_HINTERGRUND,
        ])]);

        foreach ($seite->blocks()->orderBy('position')->get()->values() as $position => $block) {
            $block->update(['position' => $position]);
        }
    }
}
