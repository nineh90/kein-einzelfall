<?php

use App\Models\Page;
use Database\Seeders\StartseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Startseite, Abschnitt „Verein“: neuer zweiter Absatz von Taddi (KEV-288).
 * Der erste bleibt.
 *
 * Nur wo der zweite Absatz noch der alte ist. Danach die englische Fassung
 * neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ALT = 'Wir schaffen Verbindungen zwischen Betroffenen, Angehörigen und '
        .'Interessierten und fördern den Austausch auf Augenhöhe mit Fachpersonen.';

    public function up(): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
                $absaetze = $block->data['absaetze'] ?? null;

                if (is_array($absaetze) && ($absaetze[1] ?? null) === self::ALT) {
                    $absaetze[1] = StartseiteSeeder::VEREIN_ZWEITER_ABSATZ;
                    $block->update(['data' => array_replace($block->data, ['absaetze' => $absaetze])]);
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
