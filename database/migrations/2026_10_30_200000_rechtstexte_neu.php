<?php

use App\Models\Page;
use Database\Seeders\RechtstexteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Datenschutzerklärung und Impressum neu, geschrieben für diese Website
 * (Kevin, 30.10.2026: online stellen, Taddi liest gegen). Ersetzt beide
 * Seiten vollständig. Danach die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seiten = Page::where('locale', 'de')
            ->whereIn('slug', [RechtstexteSeeder::DATENSCHUTZ_SLUG, RechtstexteSeeder::IMPRESSUM_SLUG])
            ->get();

        foreach ($seiten as $seite) {
            RechtstexteSeeder::setzen($seite);
        }

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
