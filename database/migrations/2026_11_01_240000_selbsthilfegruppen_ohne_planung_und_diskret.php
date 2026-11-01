<?php

use App\Models\Page;
use Database\Seeders\TeamUndGruppenSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * /selbsthilfegruppen (KEV-309): keine Gliederung nach „In Planung“ mehr,
 * und die sechste Gruppe „DISkret unter uns“ (Taddis Text und Bild aus dem
 * Drive). Danach die englische Fassung neu, wie bei allen Seiten seit
 * 08.10.2026 (die Gruppe bekommt dabei ihre Übersetzung).
 */
return new class extends Migration
{
    public function up(): void
    {
        TeamUndGruppenSeeder::diskretNachtragen();
        TeamUndGruppenSeeder::zielgruppenSetzen();
        TeamUndGruppenSeeder::selbsthilfeOhneStatus();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
