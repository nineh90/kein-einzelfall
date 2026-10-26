<?php

use App\Models\Page;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Über uns, Abschnitt „Herr und Frau Unbekannt“ (KEV-185): wieder
 * linksbündig wie „Team“, rechts daneben das Fragezeichen im Kreis. Die
 * Karte darunter bleibt mittig. Danach die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seite = Page::where('slug', 'ueber-uns-vorstand-und-team')->where('locale', 'de')->first();

        foreach ($seite?->blocks()->where('typ', 'text')->get() ?? [] as $block) {
            if (($block->data['titel'] ?? null) === 'Herr und Frau Unbekannt' && empty($block->data['symbol'])) {
                $daten = $block->data;
                unset($daten['mittig']);
                $block->update(['data' => $daten + ['symbol' => 'circle-help']]);
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
