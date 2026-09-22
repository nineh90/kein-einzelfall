<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Verweist von /wissen auf REHADAT-Recht.
 *
 * Ergebnis von KEV-4 (siehe docs/KEV-4-REHADAT.md): Beim Schwerbehindertenrecht
 * ist REHADAT mit 16.710 dokumentierten Urteilen erschöpfend — eine eigene
 * Sammlung daneben wäre Doppelarbeit, die der Verein nie aktuell hielte. Beim
 * Sozialen Entschädigungsrecht, dem Kernthema des Vereins, ist REHADAT dünn;
 * dort entsteht später die eigene Bibliothek.
 *
 * /wissen ist derzeit die einzige passende Stelle. Die Seiten, auf denen ein
 * solcher Verweis inhaltlich ebenfalls hingehörte — Schwerbehindertenausweis,
 * GdB, Merkzeichen — gibt es noch nicht; /das-hilfesystem und
 * /buerokratie-labyrinth sind entgegen ihrem Namen Veranstaltungsankündigungen.
 * Kommen die Themenseiten, gehört je ein Verweis dazu.
 *
 * Der Text stammt von uns und nicht vom Verein — festgehalten in der
 * Übergabe-Checkliste, damit er gegengelesen wird.
 */
return new class extends Migration
{
    private const SLUG = 'wissen';

    public function up(): void
    {
        $seite = Page::where('slug', self::SLUG)->where('locale', 'de')->first();

        if (! $seite) {
            return;
        }

        // Nicht doppelt anlegen: Migrationen laufen zwar nur einmal, aber eine
        // Datenbank, die aus dem Seeder frisch aufgebaut wurde, kann den
        // Baustein bereits tragen.
        $vorhanden = $seite->blocks()
            ->where('typ', 'hinweis')
            ->get()
            ->contains(fn ($block) => str_contains(
                $block->data['link']['url'] ?? '',
                'rehadat-recht.de',
            ));

        if ($vorhanden) {
            return;
        }

        $seite->blocks()->create([
            'typ' => 'hinweis',
            'position' => (int) $seite->blocks()->max('position') + 1,
            'data' => [
                'titel' => 'Urteile zum Schwerbehindertenrecht',
                'text' => 'Zu Grad der Behinderung, Merkzeichen und '
                    .'Feststellungsverfahren sammelt REHADAT-Recht die '
                    .'Rechtsprechung — ein vom Bundesministerium für Arbeit und '
                    .'Soziales gefördertes Angebot mit über 16.000 Urteilen, '
                    .'viele davon zusätzlich in Einfacher Sprache erklärt.',
                'art' => 'hinweis',
                'link' => [
                    'label' => 'Zu REHADAT-Recht',
                    'url' => 'https://www.rehadat-recht.de/rechtsprechung/',
                ],
            ],
        ]);
    }

    public function down(): void
    {
        $seite = Page::where('slug', self::SLUG)->where('locale', 'de')->first();

        $seite?->blocks()
            ->where('typ', 'hinweis')
            ->get()
            ->filter(fn ($block) => str_contains($block->data['link']['url'] ?? '', 'rehadat-recht.de'))
            ->each->delete();
    }
};
