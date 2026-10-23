<?php

use App\Models\GlossaryTerm;
use App\Models\Page;
use Database\Seeders\AbkuerzungenSexuellerKontextSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Glossar „Abkürzungen im sexuellen Kontext“ unter „Wissen“ (KEV-154). Legt
 * nur an, was fehlt. Danach die englische Fassung, wie bei allen Seiten seit
 * 08.10.2026 (nur auf einer eingerichteten Datenbank, siehe
 * englisch_vollstaendig).
 */
return new class extends Migration
{
    public function up(): void
    {
        AbkuerzungenSexuellerKontextSeeder::anlegen();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        Page::where('slug', AbkuerzungenSexuellerKontextSeeder::SLUG)->each(function (Page $seite) {
            GlossaryTerm::where('seite_gruppe', $seite->uebersetzungs_gruppe)->delete();
            $seite->blocks()->delete();
            $seite->delete();
        });
    }
};
