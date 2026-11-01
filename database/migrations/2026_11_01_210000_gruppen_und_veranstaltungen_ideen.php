<?php

use App\Models\Page;
use Database\Seeders\GruppenUndVeranstaltungenSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * /gruppen-und-veranstaltungen: Kasten „Deine Ideen sind gefragt!“ unter
 * der Einleitung (KEV-290), siehe GruppenUndVeranstaltungenSeeder::ideenKasten().
 * Danach die englische Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        GruppenUndVeranstaltungenSeeder::ideenKasten();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
