<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Kinderkodex: neuer Text von Taddi (KEV-99). Nur, wo noch der Text der
 * Altseite steht (ein Absatz ohne Überschrift, die Sätze dort ohne
 * Leerzeichen aneinandergeklebt).
 */
return new class extends Migration
{
    private const ALT = 'Kinder brauchen Schutz, Vertrauen und Erwachsene, die hinsehen. Bei KE!N EINZELFALL e. V. ist e'
        .'s uns ein Herzensanliegen, dass Kinder und Jugendliche überall dort sicher sind, wo wir wirken '
        .'– online wie offline. Viele von ihnen tragen Erfahrungen oder Belastungen mit sich, die sie nie'
        .'mals hätten erleben dürfen. Deshalb schaffen wir Räume, in denen sie respektiert, gestärkt und '
        .'verlässlich geschützt werden.Unser Kinderkodex zeigt, wie wir diesen Schutz leben: klar, verbin'
        .'dlich und mit voller Verantwortung. Er legt fest, wie wir Risiken vorbeugen, wie wir reagieren '
        .'und wie wir sicherstellen, dass die Rechte und die Würde von Kindern jederzeit im Mittelpunkt s'
        .'tehen.Hier findest du unseren vollständigen Kinderkodex – ein Versprechen, das wir jeden Tag ei'
        .'nlösen.';

    public function up(): void
    {
        $this->tauschen([self::ALT], AltseiteSeeder::NEUE_TEXTE['kinderkodex']['']);
    }

    public function down(): void
    {
        $this->tauschen(AltseiteSeeder::NEUE_TEXTE['kinderkodex'][''], [self::ALT]);
    }

    private function tauschen(array $von, array $nach): void
    {
        foreach (Page::where('slug', 'kinderkodex')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
                if (blank($block->data['titel'] ?? null) && ($block->data['absaetze'] ?? null) === $von) {
                    $block->update(['data' => array_replace($block->data, ['absaetze' => $nach])]);
                }
            }
        }
    }
};
