<?php

use App\Models\Page;
use Database\Seeders\AnuasSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Projekt „ANUAS“ unter „Projekte“ mit Taddis Text (KEV-162). Legt nur an,
 * was fehlt. Danach die englische Fassung (nur auf einer eingerichteten
 * Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        AnuasSeeder::anlegen();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        $gruppe = Page::where('slug', AnuasSeeder::SLUG)->where('locale', 'de')->value('uebersetzungs_gruppe');

        Page::where('uebersetzungs_gruppe', $gruppe)->each(function (Page $seite) {
            $seite->blocks()->delete();
            $seite->delete();
        });
    }
};
