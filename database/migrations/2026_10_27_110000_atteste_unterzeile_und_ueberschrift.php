<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\AttesteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Atteste (KEV-243, KEV-244): Unterzeile „Wenn Worte allein nicht reichen“,
 * grüne Überschrift über der Einleitung und ein Absatz mit der
 * Kontaktadresse. Unterzeile nur, wo noch keine steht. Danach die englische
 * Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        AttesteSeeder::nachtragen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }

        Titelbilder::setzen();
    }

    public function down(): void
    {
        // Bewusst nichts, wie bei den übrigen Inhalts-Migrationen.
    }
};
