<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Mitgliedschaft, Abschnitt „Wir brauchen dich!“: Der Einstieg und die
 * Stichworte („Eigene Erfahrungen“, „Netzwerk erweitern“ …) sind fett, wie
 * von Taddi in Anführungszeichen markiert (KEV-126). Der Text bleibt.
 *
 * Nur wo noch genau Taddis Text von KEV-93 ohne Hervorhebung steht. Danach
 * die englische Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const TITEL = 'Wir brauchen dich!';

    public function up(): void
    {
        $neu = AltseiteSeeder::NEUE_TEXTE['mitgliedschaft'][self::TITEL];
        $ohne = array_map(fn (string $absatz) => str_replace('*', '', $absatz), $neu);

        foreach (Page::where('slug', 'mitgliedschaft')->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
                if (($block->data['titel'] ?? null) === self::TITEL && ($block->data['absaetze'] ?? null) === $ohne) {
                    $block->update(['data' => array_replace($block->data, ['absaetze' => $neu])]);
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
