<?php

use App\Models\Page;
use App\Support\Textpflege;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Beim Import der Altseite verlorene Inhalte (Prüfung der Firma, 08.10.2026):
 * Anmeldeknöpfe und -adressen der Veranstaltungen, die Beschriftungen der
 * Kontaktseite, Dokumenttitel in Grossbuchstaben.
 *
 * Nur wo noch der Stand der Altseite steht: Kontakt-Abschnitte nur, wenn die
 * Absätze noch genau die der Altseite sind; Knöpfe nur, wo noch keiner ist.
 */
return new class extends Migration
{
    public function up(): void
    {
        $alt = json_decode(file_get_contents(base_path('docs/altseite-inhalt.json')), true)['/kontakt/']['bloecke'] ?? [];
        $altKontakt = collect($alt)->mapWithKeys(fn ($b) => [$b['titel'] ?? '' => $b['absaetze']]);

        foreach (Page::where('locale', 'de')->with('blocks')->get() as $seite) {
            foreach ($seite->blocks as $block) {
                $data = $block->data ?? [];
                $titel = $data['titel'] ?? '';

                if ($block->typ === 'text') {
                    $neu = AltseiteSeeder::NEUE_TEXTE[$seite->slug][$titel] ?? null;
                    if ($seite->slug === 'kontakt' && $neu && ($data['absaetze'] ?? null) === ($altKontakt[$titel] ?? false)) {
                        $data['absaetze'] = $neu;
                    }

                    $knopf = AltseiteSeeder::NEUE_KNOEPFE[$seite->slug][$titel] ?? null;
                    if ($knopf && empty($data['cta'])) {
                        $data['cta'] = $knopf;
                    }
                }

                if ($block->typ === 'download_list') {
                    $data['dokumente'] = array_map(function ($d) {
                        $d['titel'] = AltseiteSeeder::DOKUMENT_KORREKTUREN[$d['titel'] ?? ''] ?? ($d['titel'] ?? null);

                        return $d;
                    }, $data['dokumente'] ?? []);
                }

                $data = Textpflege::bausteinDaten($data, $seite->slug);

                if ($data !== ($block->data ?? [])) {
                    $block->update(['data' => $data]);
                }
            }
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
