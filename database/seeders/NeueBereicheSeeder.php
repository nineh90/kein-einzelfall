<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Die vier Bereiche aus dem Strukturpapier des Vereins, die es auf der
 * Altseite noch nicht gibt: Schutzkonzept, Beschwerdemanagement, Projekte und
 * Publikationen.
 *
 * ── Warum sie als Entwurf angelegt werden ───────────────────────────────────
 *
 * Sie sind leer. Das ist der Punkt.
 *
 * Vertraglich gilt: Texte stellt der Verein, wir pflegen sie ein. Diese vier
 * Seiten haben noch keinen Text — sie stehen bisher nur als Überschrift im
 * Strukturpapier. Sie hier mit erfundenen Sätzen zu füllen wäre bei einem
 * Schutzkonzept nicht bloss unsauber, sondern gefährlich: Ein Schutzkonzept
 * ist eine Selbstverpflichtung, und was darin steht, muss der Verein auch
 * einhalten können.
 *
 * Angelegt werden sie trotzdem, und zwar als Entwurf: So stehen sie im Panel
 * als Arbeitsliste, mit der richtigen Adresse und der richtigen Struktur —
 * und auf der Website taucht so lange nichts auf, bis jemand sie freigibt.
 *
 * Ins Menü kommen sie erst danach (config/navigation.php). Ein Menüpunkt auf
 * einen Entwurf wäre ein 404.
 */
class NeueBereicheSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::seiten() as $seite) {
            $datensatz = Page::updateOrCreate(
                ['slug' => $seite['slug']],
                [
                    'titel' => $seite['titel'],
                    'meta_title' => $seite['titel'].' - Kein Einzelfall e.V.',
                    // Entwurf: für Besucher nicht sichtbar.
                    'published_at' => null,
                ],
            );

            $datensatz->blocks()->delete();

            foreach ($seite['abschnitte'] as $position => $titel) {
                $datensatz->blocks()->create([
                    'typ' => 'text',
                    'position' => $position,
                    // Nur die Überschrift, keine Absätze. Die schreibt der Verein.
                    'data' => array_filter(['titel' => $titel]),
                ]);
            }
        }

        $this->command?->info(count(self::seiten()).' Bereiche als Entwurf angelegt.');
    }

    /**
     * Die Zwischenüberschriften stammen wörtlich aus dem Strukturpapier des
     * Vereins (Abschnitte 5 und 12). Wo dort nur der Bereichsname steht —
     * Schutzkonzept, Beschwerdemanagement —, steht hier auch nur ein leerer
     * Abschnitt: Eine Gliederung zu erfinden hiesse, dem Verein vorzugeben,
     * was in seinem Schutzkonzept zu stehen hat.
     *
     * @return array<int, array{slug: string, titel: string, abschnitte: array<int, ?string>}>
     */
    public static function seiten(): array
    {
        return [
            [
                'slug' => 'schutzkonzept',
                'titel' => 'Schutzkonzept',
                'abschnitte' => [null],
            ],
            [
                'slug' => 'beschwerdemanagement',
                'titel' => 'Beschwerdemanagement',
                'abschnitte' => [null],
            ],
            [
                'slug' => 'projekte',
                'titel' => 'Projekte',
                'abschnitte' => ['Laufende Projekte', 'Abgeschlossene Projekte'],
            ],
            [
                'slug' => 'publikationen',
                'titel' => 'Publikationen',
                'abschnitte' => ['Umfragen', 'Petitionen', 'Broschüren', 'Print-Medien'],
            ],
        ];
    }
}
