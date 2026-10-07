<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Startseite: Das Band „Unterstützung“ rückt unter „Verein“ und „Mitglieder“,
 * vor die Hilfe-Nummern (KEV-106). Bisher stand es direkt unter der
 * Spendenmöglichkeit, dort stand zweimal „Spenden“ untereinander.
 *
 * Nur wenn das Band noch direkt hinter der Spendenmöglichkeit steht. Hat der
 * Verein die Reihenfolge im Panel schon selbst geändert, bleibt sie.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->jeStartseite(function (array $bloecke) {
            $typen = array_column($bloecke, 'typ');
            $band = array_search('cta_band', $typen, true);

            if ($band === false || ($typen[$band - 1] ?? null) !== 'donation_options') {
                return null;
            }

            $eintrag = array_splice($bloecke, $band, 1)[0];

            // Hinter den letzten Textabschnitt vor der Spendenmöglichkeit
            // („Mitglieder“), also vor die Hilfe-Nummern.
            $typen = array_column($bloecke, 'typ');
            $spenden = array_search('donation_options', $typen, true);
            $ziel = null;
            for ($i = $spenden - 1; $i >= 0; $i--) {
                if ($typen[$i] === 'text') {
                    $ziel = $i + 1;
                    break;
                }
            }

            if ($ziel === null) {
                return null;
            }

            array_splice($bloecke, $ziel, 0, [$eintrag]);

            return $bloecke;
        });
    }

    public function down(): void
    {
        $this->jeStartseite(function (array $bloecke) {
            $typen = array_column($bloecke, 'typ');
            $band = array_search('cta_band', $typen, true);

            if ($band === false || ($typen[$band - 1] ?? null) !== 'text') {
                return null;
            }

            $eintrag = array_splice($bloecke, $band, 1)[0];
            $spenden = array_search('donation_options', array_column($bloecke, 'typ'), true);

            if ($spenden === false) {
                return null;
            }

            array_splice($bloecke, $spenden + 1, 0, [$eintrag]);

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

            foreach ($neu as $position => $block) {
                $seite->blocks()->whereKey($block['id'])->update(['position' => $position]);
            }
        }
    }
};
