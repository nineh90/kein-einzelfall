<?php

use App\Models\Page;
use Database\Seeders\OeffentlichkeitsarbeitSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Öffentlichkeitsarbeit: Der Abschnitt „Messen“ heißt „Ehrenamtsmessen“ und
 * bekommt Taddis Einleitung und Bild (KEV-131). Nur, wo er noch unverändert
 * ist. Danach die englische Fassung, wie bei allen Seiten seit 08.10.2026
 * (nur auf einer eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        OeffentlichkeitsarbeitSeeder::messenAbschnittErgaenzen();

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
