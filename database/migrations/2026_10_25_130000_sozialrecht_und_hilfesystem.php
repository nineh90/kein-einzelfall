<?php

use App\Models\GlossaryTerm;
use App\Models\Page;
use Database\Seeders\SozialrechtUndHilfesystemSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Glossar „Sozialrecht & Hilfesystem“ unter „Wissen“ (KEV-261), mit Taddis
 * typischen Sätzen darunter (KEV-262). Legt nur an, was fehlt. Danach die
 * englische Fassung (nur auf einer eingerichteten Datenbank, siehe
 * englisch_vollstaendig).
 */
return new class extends Migration
{
    public function up(): void
    {
        SozialrechtUndHilfesystemSeeder::anlegen();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        Page::where('slug', SozialrechtUndHilfesystemSeeder::SLUG)->each(function (Page $seite) {
            GlossaryTerm::where('seite_gruppe', $seite->uebersetzungs_gruppe)->delete();
            $seite->blocks()->delete();
            $seite->delete();
        });
    }
};
