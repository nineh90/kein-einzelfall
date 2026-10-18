<?php

use App\Models\Page;
use Database\Seeders\UebersetzungenSeeder;
use Database\Seeders\UnterstuetzungsbandSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Das Band „Unterstützung“ wandert von der Startseite auf einzelne
 * Unterseiten (KEV-120). Auf der Startseite folgte kurz darauf „Jetzt
 * spenden“, es wurde zweimal hintereinander zum Spenden aufgerufen.
 *
 * Von der Startseite in allen Sprachen, ohne Blick auf den Text: Es gab dort
 * immer nur dieses eine Band. Welche Unterseiten es bekommen, steht im
 * UnterstuetzungsbandSeeder. Danach die englische Fassung neu, wie bei allen
 * Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'cta_band')->get() as $block) {
                $block->delete();
            }
        }

        UnterstuetzungsbandSeeder::anhaengen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
