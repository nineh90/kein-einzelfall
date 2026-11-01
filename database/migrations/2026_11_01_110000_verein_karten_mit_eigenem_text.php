<?php

use App\Models\Page;
use Database\Seeders\UebersetzungenSeeder;
use Database\Seeders\VereinsseiteSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Vereinsseite: Unter den Karten Selbsthilfe- und Arbeitsgruppen Taddis
 * eigener Text statt der Unterzeile der Seiten (KEV-293). Den Namen setzt
 * die Ansicht ins Bild. Nur wo der Baustein noch so dasteht wie seit
 * KEV-174. Danach die englische Fassung neu, wie bei allen Seiten seit
 * 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        VereinsseiteSeeder::kartenMitEigenemText();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
