<?php

use App\Models\Page;
use Database\Seeders\GlossarSozialrechtSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Glossar mit Taddis Abkürzungen aus dem Sozialrecht und ihrer Einleitung
 * (KEV-153). Legt nur an, was fehlt. Danach die englische Fassung, wie bei
 * allen Seiten seit 08.10.2026 (nur auf einer eingerichteten Datenbank, siehe
 * englisch_vollstaendig).
 */
return new class extends Migration
{
    public function up(): void
    {
        GlossarSozialrechtSeeder::anlegen();

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
