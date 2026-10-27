<?php

use App\Models\Page;
use Database\Seeders\AnfragenSeiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Anfragen & Austausch nach Taddis Tickets (KEV-274 bis KEV-276): Titel und
 * Unterzeile, neue Texte, die Freigabe für Unterlagen neben dem Ablauf und
 * die Krisendienste vor dem Formular. Danach die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        AnfragenSeiteSeeder::feinschliff();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts, wie bei den übrigen Inhalts-Migrationen.
    }
};
