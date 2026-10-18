<?php

use App\Models\Page;
use Database\Seeders\StartseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Startseite: Die Überschrift über den Einstiegskarten endet mit
 * Auslassungspunkten, „Was wir gemeinsam bewegen …“ (KEV-111, Wunsch von
 * Taddi).
 *
 * Nur wo noch die Überschrift ohne Punkte steht. Danach die englische
 * Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'quick_access')->get() as $block) {
                if (($block->data['titel'] ?? null) === 'Was wir gemeinsam bewegen') {
                    $block->update(['data' => array_replace($block->data, [
                        'titel' => StartseiteSeeder::KARTEN_UEBERSCHRIFT,
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
