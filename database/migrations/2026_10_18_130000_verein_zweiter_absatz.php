<?php

use App\Models\Page;
use Database\Seeders\StartseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Startseite, Abschnitt „Verein“: ein zweiter Absatz von Taddi am Ende
 * (KEV-117). Der erste bleibt, wie er seit KEV-54 steht.
 *
 * Nur wo der Abschnitt noch genau diesen einen Absatz hat. Danach die
 * englische Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ERSTER = '*KE!N EINZELFALL e.V. wurde 2024 aus persönlicher Betroffenheit heraus '
        .'gegründet.* Aus Erfahrung wurde Wissen und aus Wissen wurde Betroffenenexpertise. Heute '
        .'schaffen wir Räume für Austausch, teilen Wissen und machen sichtbar, was Betroffene bewegt.';

    public function up(): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
                if (($block->data['absaetze'] ?? null) === [self::ERSTER]) {
                    $block->update(['data' => array_replace($block->data, [
                        'absaetze' => [self::ERSTER, StartseiteSeeder::VEREIN_ZWEITER_ABSATZ],
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
