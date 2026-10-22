<?php

use App\Models\Page;
use Database\Seeders\OeffentlichkeitsarbeitSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Öffentlichkeitsarbeit: Ehrenamtsmesse Lübeck mit vier Fotos (KEV-133).
 * Nur, wenn es die Adresse noch nicht gibt. Danach die englische Fassung,
 * wie bei allen Seiten seit 08.10.2026 (nur auf einer eingerichteten
 * Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        OeffentlichkeitsarbeitSeeder::eintragAnlegen(OeffentlichkeitsarbeitSeeder::MESSE_LUEBECK);

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
