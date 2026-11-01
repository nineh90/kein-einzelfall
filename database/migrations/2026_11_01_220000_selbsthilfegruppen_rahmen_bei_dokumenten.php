<?php

use App\Models\Page;
use Database\Seeders\TeamUndGruppenSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * /selbsthilfegruppen: Der Absatz zu Teilnahmebedingungen und Grenzen geht
 * aus dem Text über und unter die Dokumentenliste daneben (KEV-291), siehe
 * TeamUndGruppenSeeder::selbsthilfeRahmenZuDokumenten(). Danach die englische
 * Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        TeamUndGruppenSeeder::selbsthilfeRahmenZuDokumenten();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
