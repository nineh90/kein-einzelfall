<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Startseite: Die Hilfe-Nummern („Du brauchst sofort jemanden zum Reden?“)
 * rücken unter „Verein“ und „Mitglieder“, direkt vor die Spendenmöglichkeit
 * (Wunsch des Vereins, KEV-45). Bisher standen sie direkt unter dem Aufmacher.
 *
 * Nur wenn der Kasten noch dort steht. Hat der Verein die Reihenfolge im
 * Panel schon selbst geändert, bleibt sie.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->jeStartseite(function (array $bloecke) {
            $typen = array_column($bloecke, 'typ');

            if (($typen[1] ?? null) !== 'hilfe_box' || $typen[0] !== 'hero') {
                return null;
            }

            $kasten = array_splice($bloecke, 1, 1)[0];

            // Vor die Spendenmöglichkeit; fehlt sie, vor das Hinweisband oder
            // den Kontaktabschluss; fehlt alles, ans Ende.
            $ziel = count($bloecke);
            foreach (['donation_options', 'cta_band', 'contact_close'] as $typ) {
                $stelle = array_search($typ, array_column($bloecke, 'typ'), true);
                if ($stelle !== false) {
                    $ziel = $stelle;
                    break;
                }
            }

            array_splice($bloecke, $ziel, 0, [$kasten]);

            return $bloecke;
        });
    }

    public function down(): void
    {
        $this->jeStartseite(function (array $bloecke) {
            $typen = array_column($bloecke, 'typ');
            $stelle = array_search('hilfe_box', $typen, true);

            if ($stelle === false || $stelle === 1 || $typen[0] !== 'hero') {
                return null;
            }

            $kasten = array_splice($bloecke, $stelle, 1)[0];
            array_splice($bloecke, 1, 0, [$kasten]);

            return $bloecke;
        });
    }

    /** @param  callable(array): ?array  $umstellen  null = nichts tun */
    private function jeStartseite(callable $umstellen): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->get() as $seite) {
            $bloecke = $seite->blocks()->orderBy('position')->get()
                ->map(fn ($block) => ['id' => $block->id, 'typ' => $block->typ])
                ->all();

            $neu = $umstellen($bloecke);

            if ($neu === null) {
                continue;
            }

            // Lückenlos neu durchnummerieren, sonst hätten zwei Bausteine
            // dieselbe Position und die Reihenfolge wäre Zufall.
            foreach ($neu as $position => $block) {
                $seite->blocks()->whereKey($block['id'])->update(['position' => $position]);
            }
        }
    }
};
