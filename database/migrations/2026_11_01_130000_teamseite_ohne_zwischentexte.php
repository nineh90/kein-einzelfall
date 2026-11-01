<?php

use App\Models\Page;
use Database\Seeders\TeamseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Teamseite: Titel mit „&“, die Texte und Symbole bei Team und Herr und Frau
 * Unbekannt weg, Herr und Frau Unbekannt mittig unter dem Team (KEV-295),
 * siehe TeamseiteSeeder::teamZusammenfassen(). Danach die englische Fassung
 * neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        TeamseiteSeeder::teamZusammenfassen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
