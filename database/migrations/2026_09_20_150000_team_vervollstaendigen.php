<?php

use App\Models\Page;
use Database\Seeders\TeamUndGruppenSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Vervollständigt Vorstand und Team und hängt die Porträts an.
 *
 * Der Abzug der Altseite vom Juli hatte durch einen Fehler im Importer
 * (AltseiteHolen::bloecke — <path> der SVG-Icons galt als <p>) vier von
 * sieben Personen verloren: Franziska Künstler, Petra Hildebrandt, Tanja
 * Häfner und André Bauer. Schlimmer: Ihre Texte landeten im Profil der
 * jeweils vorigen Person — Tatjana Belmar „sprach" über ihr Abimotto und
 * ihre Arbeit als Kassenwartin.
 *
 * Warum eine Migration: Seeder laufen bei uns nur bei leerer Datenbank und
 * auf dem Server gar nicht. Dasselbe Muster wie bei den Gruppen.
 *
 * Anders als dort wird hier bewusst über den Bestand geschrieben: Die
 * vorhandenen Profile sind falsch, nicht bloss unvollständig. Was der Verein
 * im Panel an einer Person geändert hat, ginge damit verloren — bis heute
 * gab es dort nichts zu ändern, weil die Seite noch nicht übergeben ist.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seeder = new TeamUndGruppenSeeder;
        $seeder->team();

        // Die Seite nur neu setzen, wenn sie noch so aussieht, wie der Seeder
        // sie hinterlassen hat: ein Baustein mit allen Personen. Wer sie im
        // Panel schon umgebaut hat, behält seine Fassung.
        $seite = Page::where('slug', 'ueber-uns-vorstand-und-team')->where('locale', 'de')->first();

        $raster = $seite?->blocks()->where('typ', 'team_grid')->get();
        $unveraendert = $raster && $raster->count() === 1 && empty($raster->first()->data);

        if ($unveraendert) {
            $seeder->teamseiteAufbauen();
        }
    }

    public function down(): void
    {
        /*
         * Bewusst nichts. Der vorige Stand waren vermischte Biografien —
         * dorthin führt kein Weg zurück, den irgendwer wollen könnte.
         */
    }
};
