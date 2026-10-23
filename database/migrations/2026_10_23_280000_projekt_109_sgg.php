<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\AnuasSeeder;
use Database\Seeders\Paragraf109SggSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Projekt „§ 109 SGG Gegengutachten“ unter „Projekte“ mit Taddis Text
 * (KEV-161). Dazu bekommt ANUAS statt des Platzhalters das Bild der
 * Übersicht, wie künftig alle Projekte vorerst (Titelbilder::DATEI). Ein im
 * Panel gepflegtes Bild bleibt. Legt nur an, was fehlt; danach die englische
 * Fassung (nur auf einer eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        $gruppe = Page::where('slug', AnuasSeeder::SLUG)->where('locale', 'de')->value('uebersetzungs_gruppe');

        if ($gruppe) {
            Page::where('uebersetzungs_gruppe', $gruppe)
                ->where('titelbild', Titelbilder::PLATZHALTER)
                ->update(['titelbild' => null]);
        }

        Paragraf109SggSeeder::anlegen();
        Titelbilder::setzen();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        $gruppe = Page::where('slug', Paragraf109SggSeeder::SLUG)->where('locale', 'de')->value('uebersetzungs_gruppe');

        Page::where('uebersetzungs_gruppe', $gruppe)->each(function (Page $seite) {
            $seite->blocks()->delete();
            $seite->delete();
        });

        Page::where('slug', AnuasSeeder::SLUG)
            ->where('titelbild', Titelbilder::datei(AnuasSeeder::SLUG))
            ->update(['titelbild' => Titelbilder::PLATZHALTER]);
    }
};
