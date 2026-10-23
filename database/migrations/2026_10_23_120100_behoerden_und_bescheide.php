<?php

use App\Models\GlossaryTerm;
use App\Models\Page;
use Database\Seeders\BehoerdenUndBescheideSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Zweites Glossar „Behörden & Bescheide“ unter „Wissen“ (KEV-155). Legt nur
 * an, was fehlt. Danach die englische Fassung, wie bei allen Seiten seit
 * 08.10.2026 (nur auf einer eingerichteten Datenbank, siehe
 * englisch_vollstaendig).
 */
return new class extends Migration
{
    public function up(): void
    {
        BehoerdenUndBescheideSeeder::anlegen();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        Page::where('slug', BehoerdenUndBescheideSeeder::SLUG)->each(function (Page $seite) {
            GlossaryTerm::where('seite_gruppe', $seite->uebersetzungs_gruppe)->delete();
            $seite->blocks()->delete();
            $seite->delete();
        });
    }
};
