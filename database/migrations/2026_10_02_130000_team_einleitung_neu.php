<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Teamseite: neuer Text unter „Unser Team – mit Herz, Haltung und Vision“,
 * von Taddi (KEV-64). Nur, wo noch der Text der Altseite steht.
 */
return new class extends Migration
{
    private const TITEL = 'Unser Team – mit Herz, Haltung und Vision';

    private const ALT = 'Hinter der Arbeit unseres Vereins stehen Menschen mit unterschiedlichen Erfahrungen und '
        .'Kompetenzen, die sich mit Überzeugung, fachlichem Know-how und großem persönlichen Engagement für '
        .'die Opferhilfe und die Belange von Betroffenen einsetzen. Unser Vorstand gestaltet die Arbeit des '
        .'Vereins, trifft verantwortungsvolle Entscheidungen und sorgt dafür, dass Hilfe dort ankommt, wo sie '
        .'gebraucht wird.';

    public function up(): void
    {
        $this->tauschen([self::ALT], AltseiteSeeder::NEUE_TEXTE['ueber-uns-vorstand-und-team'][self::TITEL]);
    }

    public function down(): void
    {
        $this->tauschen(AltseiteSeeder::NEUE_TEXTE['ueber-uns-vorstand-und-team'][self::TITEL], [self::ALT]);
    }

    private function tauschen(array $von, array $nach): void
    {
        foreach (Page::where('slug', 'ueber-uns-vorstand-und-team')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
                if (($block->data['titel'] ?? null) === self::TITEL && ($block->data['absaetze'] ?? null) === $von) {
                    $block->update(['data' => array_replace($block->data, ['absaetze' => $nach])]);
                }
            }
        }
    }
};
