<?php

use App\Models\Page;
use Database\Seeders\UebersetzungenSeeder;
use Database\Seeders\UnterstuetzungsbandSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Vereinsseite: statt des grünen Bands „Unterstützung“ der Spendenkasten der
 * Startseite (KEV-175, Taddi: mehr Einheitlichkeit). Wie KEV-219 auf den
 * Jahresberichten. Ein vom Verein geändertes Band bleibt. Danach die
 * englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        UnterstuetzungsbandSeeder::spendenStattBand();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
