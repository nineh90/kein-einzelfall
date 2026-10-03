<?php

use App\Models\Page;
use Database\Seeders\GruppenUndVeranstaltungenSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Bereich „Gruppen & Veranstaltungen“ (KEV-72): Übersichtsseite mit Taddis
 * Text, dazu Öffentlichkeitsarbeit und Rückblick, alle mit Platzhalterbild.
 * Legt nur an, was fehlt.
 */
return new class extends Migration
{
    public function up(): void
    {
        GruppenUndVeranstaltungenSeeder::anlegen();
    }

    public function down(): void
    {
        $slugs = array_column(GruppenUndVeranstaltungenSeeder::seiten(), 'slug');

        Page::whereIn('slug', $slugs)->each(function (Page $seite) {
            $seite->blocks()->delete();
            $seite->delete();
        });
    }
};
