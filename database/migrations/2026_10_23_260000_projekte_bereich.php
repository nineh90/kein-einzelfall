<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\ProjekteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Bereich „Projekte“ (KEV-160): Taddis Text auf der bisher leeren
 * Entwurfsseite, veröffentlicht. Ihr Titelbild ersetzt die Datei
 * projekte.webp, an der Seite ändert sich dafür nichts. Legt nur an, was
 * fehlt. Danach die englische Fassung (nur auf einer eingerichteten
 * Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        ProjekteSeeder::anlegen();
        Titelbilder::setzen();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        Page::where('slug', ProjekteSeeder::SLUG)->where('locale', 'de')->update(['published_at' => null]);
        Page::where('slug', ProjekteSeeder::SLUG)->where('locale', '!=', 'de')->each(function (Page $seite) {
            $seite->blocks()->delete();
            $seite->delete();
        });
    }
};
