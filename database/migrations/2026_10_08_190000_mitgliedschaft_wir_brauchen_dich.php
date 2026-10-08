<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Mitgliedschaft, Abschnitt „Wir brauchen dich!“: neuer Text von Taddi
 * (KEV-93). Nur, wo noch der Text der Altseite steht. Danach die englische
 * Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const TITEL = 'Wir brauchen dich!';

    /** Was sich gegenüber der Altseite ändert; die übrigen Absätze bleiben. */
    private const ALT_ANFANG = [
        'Warum solltest ausgerechnet DU bei uns im Verein Mitglied werden? Es gibt viele Gründe dafür, unserem '
        .'Verein beizutreten. Einige Motivationen sind die folgenden.',
        'Eigene Erfahrungen verarbeiten: Vielleicht bist du selbst betroffen und möchtest anderen helfen, indem du '
        .'eigene Erfahrungen im Umgang mit der Betroffenheit teilst und Unterstützung anbietest, oder vielleicht '
        .'brauchst du genau deswegen selbst Hilfe.',
    ];

    private const ALT_ENDE = 'Hilfe und Unterstützung leisten: Du möchtest Menschen in schwierigen Lebenssituationen '
        .'aktiv unterstützen und ihnen helfen, ihre Erlebnisse zu verarbeiten und neue Perspektiven zu finden.';

    public function up(): void
    {
        $this->tauschen($this->alt(), AltseiteSeeder::NEUE_TEXTE['mitgliedschaft'][self::TITEL]);

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        $this->tauschen(AltseiteSeeder::NEUE_TEXTE['mitgliedschaft'][self::TITEL], $this->alt());
    }

    /** Der Text der Altseite: Taddis Mittelteil ist unverändert übernommen. */
    private function alt(): array
    {
        $neu = AltseiteSeeder::NEUE_TEXTE['mitgliedschaft'][self::TITEL];

        return [...self::ALT_ANFANG, ...array_slice($neu, 2, -1), self::ALT_ENDE];
    }

    private function tauschen(array $von, array $nach): void
    {
        foreach (Page::where('slug', 'mitgliedschaft')->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
                if (($block->data['titel'] ?? null) === self::TITEL && ($block->data['absaetze'] ?? null) === $von) {
                    $block->update(['data' => array_replace($block->data, ['absaetze' => $nach])]);
                }
            }
        }
    }
};
