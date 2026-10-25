<?php

use App\Models\Page;
use Database\Seeders\BehoerdenUndBescheideSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * KEV-262: Taddis „Typische Sätze – einfach übersetzt“ sind keine eigene
 * Kategorie, sondern stehen unter dem Glossar ihrer Kategorie. Hier die 47
 * Sätze unter „Behörden & Bescheide“; Gericht & Verfahren, Medizinische
 * Berichte & Gutachten und Sozialrecht & Hilfesystem bekommen ihre beim
 * Anlegen (Sätze liegen schon in data/typische-saetze.json). Legt nur an,
 * was fehlt. Danach die englische Fassung (nur auf einer eingerichteten
 * Datenbank, siehe englisch_vollstaendig).
 */
return new class extends Migration
{
    public function up(): void
    {
        BehoerdenUndBescheideSeeder::saetzeAnlegen();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        Page::where('slug', BehoerdenUndBescheideSeeder::SLUG)->each(
            fn (Page $seite) => $seite->blocks()->where('typ', 'typische_saetze')->delete(),
        );
    }
};
