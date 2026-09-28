<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Startseite: Der Hinweis „Du kannst diese Seite jederzeit sofort verlassen …“
 * steht seit KEV-30 im Band unten im Aufmacher (Sprachdatei, rahmen.verlassen).
 * Im Kontaktabschluss am Seitenende fällt er weg.
 *
 * Nur wo noch der alte Wortlaut steht.
 */
return new class extends Migration
{
    private const HINWEIS = 'Du kannst diese Seite jederzeit sofort verlassen: über „Notausgang“ oben '
        .'rechts, in der Leiste unten, oder mit dreimal ESC.';

    /** Die Übersetzung aus dem Wörterbuch, steht auf der englischen Startseite. */
    private const HINWEIS_EN = 'You can leave this page immediately at any time: via „Emergency exit“ at '
        .'the top right, in the bar at the bottom, or by pressing ESC three times.';

    public function up(): void
    {
        foreach ($this->bloecke() as $block) {
            if (in_array($block->data['hinweis'] ?? null, [self::HINWEIS, self::HINWEIS_EN], true)) {
                $block->update(['data' => array_diff_key($block->data, ['hinweis' => true])]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->bloecke() as $block) {
            if (blank($block->data['hinweis'] ?? null) && $block->page->locale === 'de') {
                $neu = [];
                foreach ($block->data as $schluessel => $wert) {
                    if ($schluessel === 'ctas') {
                        $neu['hinweis'] = self::HINWEIS;
                    }
                    $neu[$schluessel] = $wert;
                }
                $block->update(['data' => $neu]);
            }
        }
    }

    private function bloecke()
    {
        return Page::where('slug', Page::STARTSEITE_SLUG)->get()
            ->flatMap(fn ($seite) => $seite->blocks()->where('typ', 'contact_close')->get());
    }
};
