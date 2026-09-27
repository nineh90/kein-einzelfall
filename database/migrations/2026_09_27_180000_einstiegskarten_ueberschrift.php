<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Startseite: Die Überschrift über den vier Einstiegskarten heißt „Was wir
 * gemeinsam bewegen“ statt „Unsere Aufgabe“ (Wunsch von Taddi, KEV-52/53).
 *
 * Nur wo noch die alte Überschrift steht. Englisch maschinell übersetzt und
 * ungeprüft.
 */
return new class extends Migration
{
    private const FASSUNGEN = [
        ['Unsere Aufgabe', 'Was wir gemeinsam bewegen'],
        ['What we do', 'What we achieve together'],
    ];

    public function up(): void
    {
        $this->tauschen(0, 1);
    }

    public function down(): void
    {
        $this->tauschen(1, 0);
    }

    private function tauschen(int $von, int $nach): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'quick_access')->get() as $block) {
                foreach (self::FASSUNGEN as $f) {
                    if (($block->data['titel'] ?? null) === $f[$von]) {
                        $block->update(['data' => array_replace($block->data, ['titel' => $f[$nach]])]);
                    }
                }
            }
        }
    }
};
