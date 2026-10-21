<?php

use App\Models\Page;
use Database\Seeders\OeffentlichkeitsarbeitSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Öffentlichkeitsarbeit mit Taddis Text und dem Baustein für Messen,
 * Petitionen und Presse (KEV-129). Füllt nur die leere Seite aus KEV-72.
 * Danach die englische Fassung, wie bei allen Seiten seit 08.10.2026 (nur
 * auf einer eingerichteten Datenbank, siehe englisch_vollstaendig).
 */
return new class extends Migration
{
    public function up(): void
    {
        OeffentlichkeitsarbeitSeeder::anlegen();

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
