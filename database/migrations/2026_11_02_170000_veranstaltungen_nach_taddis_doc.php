<?php

use App\Models\Page;
use Database\Seeders\UebersetzungenSeeder;
use Database\Seeders\VeranstaltungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * /veranstaltungen nach Taddis Doc „3. Veranstaltungen“ (KEV-317): neue
 * Einleitung, Kalender, drei Reihen und die Termine mit Datum, siehe
 * VeranstaltungenSeeder. Danach die englische Fassung neu, wie bei allen
 * Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        VeranstaltungenSeeder::anlegen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
