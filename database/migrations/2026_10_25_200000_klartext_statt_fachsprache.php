<?php

use App\Models\Page;
use Database\Seeders\GlossarSozialrechtSeeder;
use Database\Seeders\KlartextStattFachspracheSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * „Klartext statt Fachsprache“ unter „Wissen“ (KEV-254): Übersicht mit
 * Taddis Text, Suche über alle Kategorien und den sechs Glossaren als
 * Kacheln. Die Kategorie 1 heißt wie bei Taddi „Abkürzungen im Sozialrecht“
 * statt „Glossar“, nur wo der Titel noch „Glossar“ ist.
 *
 * Legt nur an, was fehlt. Danach die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        KlartextStattFachspracheSeeder::anlegen();

        Page::where('slug', GlossarSozialrechtSeeder::SLUG)->where('locale', 'de')
            ->where('titel', GlossarSozialrechtSeeder::TITEL_BIS_25_10)
            ->update([
                'titel' => GlossarSozialrechtSeeder::TITEL,
                'meta_title' => GlossarSozialrechtSeeder::TITEL.' - Kein Einzelfall e.V.',
            ]);

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        Page::where('slug', KlartextStattFachspracheSeeder::SLUG)->each(function (Page $seite) {
            $seite->blocks()->delete();
            $seite->delete();
        });
    }
};
