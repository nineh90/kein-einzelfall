<?php

use App\Models\Page;
use Database\Seeders\TriggerWarnungSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Triggerwarnung: Taddis Text statt unseres Vorschlags (KEV-109), mit dem
 * Symbol des Darstellungs-Knopfs im zweiten Absatz.
 *
 * Nur wo noch unser Vorschlag steht. Danach die englische Fassung neu, wie
 * bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ALT = [
        'Auf dieser Website geht es um Straftaten, Gewalt und ihre Folgen. Einzelne Texte, '
            .'Erfahrungsberichte und Dokumente können belastend sein oder Erinnerungen auslösen.',
        'Du entscheidest, was du liest und wann. Du kannst jederzeit aufhören, zurückgehen oder diese '
            .'Seite sofort verlassen — dafür gibt es unten einen Knopf, oben rechts den Notausgang, und du '
            .'kannst dreimal hintereinander die Taste Esc drücken.',
        'Wenn du gerade Hilfe brauchst: Die Notfallnummern stehen am Ende jeder Seite. Die '
            .'TelefonSeelsorge (116 123) ist rund um die Uhr erreichbar.',
    ];

    public function up(): void
    {
        foreach (Page::where('slug', Page::TRIGGER_SLUG)->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
                if (($block->data['absaetze'] ?? null) === self::ALT) {
                    $block->update(['data' => array_replace($block->data, [
                        'absaetze' => TriggerWarnungSeeder::ABSAETZE,
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
