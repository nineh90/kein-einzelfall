<?php

use App\Models\Page;
use Database\Seeders\OeffentlichkeitsarbeitSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Öffentlichkeitsarbeit mit drei Themenseiten (KEV-238): Ehrenamtsmessen,
 * Petitionen und Presse ziehen von der Übersicht auf eigene Seiten mit
 * Taddis Bildern als Titelbild, die Übersicht zeigt sie als Kacheln.
 * Danach die englische Fassung (nur auf einer eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        OeffentlichkeitsarbeitSeeder::themenseitenAufbauen();

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
