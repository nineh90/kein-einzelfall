<?php

use App\Models\Page;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Startseite, Abschnitt „Verein“: Fett ist nur noch der Vereinsname, nicht
 * mehr der ganze erste Satz, Wunsch von Taddi (KEV-168).
 *
 * Nur wo der erste Absatz noch genau so steht wie seit KEV-54. Danach die
 * englische Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ALT = '*KE!N EINZELFALL e.V. wurde 2024 aus persönlicher Betroffenheit heraus '
        .'gegründet.* Aus Erfahrung wurde Wissen und aus Wissen wurde Betroffenenexpertise. Heute '
        .'schaffen wir Räume für Austausch, teilen Wissen und machen sichtbar, was Betroffene bewegt.';

    private const NEU = '*KE!N EINZELFALL e.V.* wurde 2024 aus persönlicher Betroffenheit heraus '
        .'gegründet. Aus Erfahrung wurde Wissen und aus Wissen wurde Betroffenenexpertise. Heute '
        .'schaffen wir Räume für Austausch, teilen Wissen und machen sichtbar, was Betroffene bewegt.';

    public function up(): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
                $absaetze = $block->data['absaetze'] ?? [];

                if (($absaetze[0] ?? null) === self::ALT) {
                    $absaetze[0] = self::NEU;
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
