<?php

use App\Models\Page;
use Database\Seeders\StartseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Startseite, Aufmacher: „Von Betroffenen.“ in grüner Handschrift nach dem
 * Satz zum Netzwerk (KEV-286).
 *
 * Nur wo noch der bisherige Text steht. Danach die englische Fassung neu,
 * wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ALT = "Du bist auf der Informations-, Austausch- und Selbstwirksamkeitsplattform "
        ."von KE!N EINZELFALL e.V.\n"
        ."Ein zentrales Netzwerk aus Fach- und Betroffenenexpertise auf Augenhöhe.\n"
        ."Für Opfer und Mit-Opfer, Angehörige, Interessierte und Fachpersonen.\n"
        .'Für mehr Sichtbarkeit, Gehör und Unterstützung!';

    public function up(): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'hero')->get() as $block) {
                if (($block->data['text'] ?? null) === self::ALT) {
                    $block->update(['data' => array_replace($block->data, [
                        'text' => StartseiteSeeder::AUFMACHER_TEXT,
                    ])]);
                }
            }
        }

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
