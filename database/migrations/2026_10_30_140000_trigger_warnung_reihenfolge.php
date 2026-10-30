<?php

use App\Models\Page;
use Database\Seeders\TriggerWarnungSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Triggerwarnung: „Einige Inhalte können belastend sein“ vor den Absatz zum
 * Darstellungs-Knopf (KEV-285). Der Wortlaut bleibt.
 *
 * Nur wo noch die alte Reihenfolge steht. Danach die englische Fassung neu,
 * wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ALT = [
        'Auf dieser Homepage geht es um die Folgeschäden von physischen, psychischen, digitalen und '
            .'sexuellen Gewalttaten und die oft schwierigen Wege durch Hilfe-, Versorgungs- und '
            .'Entschädigungssysteme.',
        'Links am Rand findest du einen Button {darstellung}, mit dem du die Darstellung der Seite '
            .'(Schriftgröße/Kontrast etc.) verändern kannst.',
        'Einige Inhalte können belastend sein. Du entscheidest, was du wann liest. Du kannst die Seite '
            .'jederzeit über den Button unten rechts, den Notausgang oben rechts oder durch dreimaliges '
            .'Drücken der Esc-Taste verlassen.',
        'Wenn du gerade Hilfe brauchst: Notfallnummern und Hilfsangebote findest du weiter unten. Die '
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
