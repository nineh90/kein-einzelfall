<?php

use App\Models\Page;
use Database\Seeders\UebersetzungenSeeder;
use Database\Seeders\VereinsseiteSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Vereinsseite: „Gemeinsam KE!N EINZELFALL.“ in eine eigene Zeile unter den
 * Leitsatz (KEV-173, Nils' Review). Danach die englische Fassung neu, wie
 * bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        VereinsseiteSeeder::leitsatzZweizeilig();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
