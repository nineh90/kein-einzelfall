<?php

use App\Models\Page;
use Database\Seeders\TeamUndGruppenSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Über uns, Abschnitt „Herr und Frau Unbekannt“: Taddis neuer Text
 * (KEV-186). Nur wo noch der Satz der Altseite steht. Danach die englische
 * Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seite = Page::where('slug', 'ueber-uns-vorstand-und-team')->where('locale', 'de')->first();

        foreach ($seite?->blocks()->where('typ', 'text')->get() ?? [] as $block) {
            if (($block->data['absaetze'] ?? null) === [TeamUndGruppenSeeder::HINTERGRUND_TEXT_ALT]) {
                $block->update(['data' => array_replace($block->data, ['absaetze' => [TeamUndGruppenSeeder::HINTERGRUND_TEXT]])]);
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
