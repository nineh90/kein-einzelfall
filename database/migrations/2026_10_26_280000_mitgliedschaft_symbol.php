<?php

use App\Models\Page;
use Database\Seeders\MitgliedschaftsseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Mitgliedschaft: rechts neben den Dokumenten das Ausweis-Symbol im Kreis
 * (KEV-203), siehe MitgliedschaftsseiteSeeder. Danach die englische Fassung
 * neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        MitgliedschaftsseiteSeeder::fuellen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
