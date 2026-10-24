<?php

use App\Models\Page;
use Database\Seeders\GremiumUkfbSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Gremium UKFB (KEV-220): im Seitenkopf groß „Unabhängiges Kuratorium für
 * Betroffenenexpertise“, darunter „Assoziiertes Fachgremium“. Danach die
 * englische Fassung (nur auf einer eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        GremiumUkfbSeeder::seitenkopf();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
