<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\RueckblickSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Rückblick: Taddis Einleitung über den Veranstaltungen und ihr Titelbild
 * statt des Platzhalters (KEV-137). Die Beschreibung für Suchmaschinen nennt
 * nicht mehr die neueste Veranstaltung, sondern ihren Satz über die Seite.
 *
 * Nur, was noch so steht, wie wir es angelegt hatten: ein im Panel gepflegtes
 * Bild oder eine eigene Beschreibung bleiben, die Einleitung kommt nur dazu,
 * wenn sie fehlt. Danach die englische Fassung (nur auf einer eingerichteten
 * Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        $gruppe = Page::where('slug', RueckblickSeeder::SLUG)->where('locale', 'de')->value('uebersetzungs_gruppe');

        if (! $gruppe) {
            return;
        }

        Page::where('uebersetzungs_gruppe', $gruppe)
            ->where('titelbild', Titelbilder::PLATZHALTER)
            ->update(['titelbild' => null]);

        Titelbilder::setzen();

        Page::where('uebersetzungs_gruppe', $gruppe)->where('locale', 'de')
            ->where('meta_description', RueckblickSeeder::META_DESCRIPTION_ALT)
            ->update(['meta_description' => RueckblickSeeder::META_DESCRIPTION]);

        RueckblickSeeder::anlegen();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        $gruppe = Page::where('slug', RueckblickSeeder::SLUG)->where('locale', 'de')->value('uebersetzungs_gruppe');

        Page::where('uebersetzungs_gruppe', $gruppe)
            ->where('titelbild', Titelbilder::datei(RueckblickSeeder::SLUG))
            ->update(['titelbild' => Titelbilder::PLATZHALTER]);

        Page::where('uebersetzungs_gruppe', $gruppe)->where('locale', 'de')
            ->where('meta_description', RueckblickSeeder::META_DESCRIPTION)
            ->update(['meta_description' => RueckblickSeeder::META_DESCRIPTION_ALT]);

        Page::where('uebersetzungs_gruppe', $gruppe)->each(function (Page $seite) {
            $seite->blocks->filter(fn ($b) => $b->typ === 'text'
                && ($b->data['absaetze'] ?? null) === RueckblickSeeder::EINLEITUNG)
                ->each->delete();
        });
    }
};
