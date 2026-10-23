<?php

use App\Models\Page;
use Database\Seeders\RaumFuerUngesagtesSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * „Raum für Ungesagtes“ (KEV-156): Text, Kacheln und Triggerwarnung der
 * Übersicht, dazu „Wort für Wort“. Legt nur an, was fehlt. Danach die
 * englische Fassung, wie bei allen Seiten seit 08.10.2026 (nur auf einer
 * eingerichteten Datenbank, siehe englisch_vollstaendig).
 */
return new class extends Migration
{
    public function up(): void
    {
        RaumFuerUngesagtesSeeder::bereichEinrichten();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        Page::where('slug', RaumFuerUngesagtesSeeder::WORT_SLUG)->each(function (Page $seite) {
            $seite->blocks()->delete();
            $seite->delete();
        });

        Page::where('slug', RaumFuerUngesagtesSeeder::BEREICH_SLUG)->each(function (Page $seite) {
            $seite->blocks()->delete();
            $seite->update(['noindex' => true, 'triggerwarnung' => null]);
        });
    }
};
