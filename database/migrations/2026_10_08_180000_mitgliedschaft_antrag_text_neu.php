<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Mitgliedschaft, Abschnitt „Antrag auf Mitgliedschaft“: neuer Text von
 * Taddi (KEV-94). Nur, wo noch der Text der Altseite steht. Danach die
 * englische Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const TITEL = 'Antrag auf Mitgliedschaft';

    private const ALT = [
        'Für alle zukünftigen Mitglieder und Interessierten: Hier findet ihr unsere Beitrags- und '
        .'Mitgliederordnung. Denn mit dem Wunsch, Teil von KE!N EINZELFALL e.V. zu werden, beginnt mehr als '
        .'nur eine Mitgliedschaft – es ist ein klares Zeichen für Solidarität, Haltung und aktives '
        .'Mitgestalten. Damit dieser Weg für alle transparent und fair ist, haben wir unsere Beitrags- und '
        .'Mitgliederordnung veröffentlicht und mit ihr eine Basis für unser gemeinsames Engagement '
        .'geschaffen. Sie regelt alle wichtigen Fragen rund um die Mitgliedschaft, von den Voraussetzungen bis '
        .'zu den Rechten, Pflichten und der Kündigung.',
        'Jetzt mitmachen und ein Zeichen setzen!',
    ];

    public function up(): void
    {
        $this->tauschen(self::ALT, AltseiteSeeder::NEUE_TEXTE['mitgliedschaft'][self::TITEL]);

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        $this->tauschen(AltseiteSeeder::NEUE_TEXTE['mitgliedschaft'][self::TITEL], self::ALT);
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
