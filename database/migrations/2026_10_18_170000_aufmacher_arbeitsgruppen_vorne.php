<?php

use App\Models\Page;
use Database\Seeders\StartseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Startseite, Aufmacher: „Arbeitsgruppen“ und „Anfragen & Austausch“ tauschen
 * die Plätze (KEV-113, Wunsch von Taddi).
 *
 * Erkannt wird der Stand an den Zielen der Knöpfe, nicht am genauen
 * Wortlaut: Ein Vergleich ganzer Knopf-Listen scheitert schon an einem
 * zusätzlichen Feld. Auf der Demo fehlte nach KEV-86 zeitweise der Knopf
 * „Arbeitsgruppen“ (vermutlich ein Cache), darum auch der Fall davor. Zwei
 * Fälle:
 *
 *   - Stand nach KEV-86 (Anfragen, Selbsthilfe, Arbeitsgruppen): tauschen.
 *     Beschriftungen bleiben, die Varianten bleiben an ihrem Platz.
 *   - Stand davor (Anfragen, Selbsthilfe): gleich die drei neuen Knöpfe.
 *
 * Alles andere hat der Verein im Panel selbst eingerichtet und bleibt.
 * Danach die englische Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'hero')->get() as $block) {
                $ctas = array_values($block->data['ctas'] ?? []);
                $neu = match (array_column($ctas, 'url')) {
                    ['/anfragen', '/selbsthilfegruppen', '/arbeitsgruppen'] => [
                        array_replace($ctas[2], ['variant' => $ctas[0]['variant'] ?? 'primary']),
                        $ctas[1],
                        array_replace($ctas[0], ['variant' => $ctas[2]['variant'] ?? 'ghost']),
                    ],
                    ['/anfragen', '/selbsthilfegruppen'] => StartseiteSeeder::AUFMACHER_KNOEPFE,
                    default => null,
                };

                if ($neu !== null) {
                    $block->update(['data' => array_replace($block->data, ['ctas' => $neu])]);
                }
            }
        }

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
