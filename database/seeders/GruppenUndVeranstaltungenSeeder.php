<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * Bereich „Gruppen & Veranstaltungen“ (KEV-72, vorher „Gruppen & Termine“).
 *
 * Drei neue Seiten: die Übersicht des Bereichs mit Taddis Text, dazu
 * Öffentlichkeitsarbeit und Rückblick. Für die beiden gibt es noch keinen
 * Text, er kommt in eigenen Tickets. Sie stehen trotzdem schon im Menü
 * (Kevin, 03.10.2026) und sind deshalb veröffentlicht, aber für Suchmaschinen
 * gesperrt, bis Text da ist. Alle drei tragen vorerst den Platzhalter.
 * Öffentlichkeitsarbeit hat seit KEV-129 Text (OeffentlichkeitsarbeitSeeder).
 *
 * Legt nur an, was fehlt. Eine Seite, die es schon gibt, bleibt, wie sie ist:
 * Danach gehört sie dem Verein.
 */
class GruppenUndVeranstaltungenSeeder extends Seeder
{
    /** Taddis Text für die Übersicht, Reihenfolge wie bei ihr. */
    public const UEBERSICHT = [
        'titel' => 'Wo Menschen zusammenkommen, Ideen wachsen und Erfahrungen geteilt werden.',
        'absaetze' => [
            'In diesem Bereich findest du unsere Selbsthilfegruppen und Arbeitsgruppen, aktuelle Veranstaltungen sowie Einblicke in unsere Öffentlichkeitsarbeit. Außerdem schauen wir zurück auf besondere Termine, Projekte und Begegnungen, die unsere Arbeit geprägt haben.',
            'So entsteht ein Überblick darüber, wo du mitmachen kannst, was gerade passiert und was bereits entstanden ist.',
            'Entdecke unsere Gruppen, Veranstaltungen und Rückblicke.',
        ],
        'hand' => 'Das Team von KE!N EINZELFALL e.V.',
    ];

    /**
     * @return array<int, array{slug: string, titel: string, meta_description: ?string, noindex: bool, bloecke: array<int, array>}>
     */
    public static function seiten(): array
    {
        return [
            [
                'slug' => 'gruppen-und-veranstaltungen',
                'titel' => 'Gruppen & Veranstaltungen',
                'meta_description' => 'Selbsthilfegruppen, Arbeitsgruppen, Veranstaltungen und Öffentlichkeitsarbeit '
                    .'des KE!N EINZELFALL e.V. Wo du mitmachen kannst und was gerade passiert.',
                'noindex' => false,
                'bloecke' => [self::UEBERSICHT],
            ],
            [
                'slug' => 'oeffentlichkeitsarbeit',
                'titel' => 'Öffentlichkeitsarbeit',
                'meta_description' => null,
                'noindex' => true,
                'bloecke' => [],
            ],
            [
                'slug' => 'rueckblick',
                'titel' => 'Rückblick',
                'meta_description' => null,
                'noindex' => true,
                'bloecke' => [],
            ],
        ];
    }

    public function run(): void
    {
        self::anlegen();
    }

    public static function anlegen(): void
    {
        foreach (self::seiten() as $seite) {
            $datensatz = Page::firstOrCreate(
                ['slug' => $seite['slug'], 'locale' => 'de'],
                [
                    'titel' => $seite['titel'],
                    'meta_title' => $seite['titel'].' - Kein Einzelfall e.V.',
                    'meta_description' => $seite['meta_description'],
                    'noindex' => $seite['noindex'],
                    'published_at' => now(),
                ],
            );

            if ($datensatz->wasRecentlyCreated) {
                foreach ($seite['bloecke'] as $position => $daten) {
                    $datensatz->blocks()->create(['typ' => 'text', 'position' => $position, 'data' => $daten]);
                }
            }
        }

        Titelbilder::platzhalterSetzen();
        self::kalenderNebenEinleitung();
    }

    /**
     * KEV-224, Taddis Wunsch: rechts neben der Einleitung der Übersicht ein
     * Kalender mit den Terminen des Vereins (Baustein „kalender“, Schalter
     * „neben_text“). Nur wo noch keiner steht.
     */
    public static function kalenderNebenEinleitung(): void
    {
        $seite = Page::where('slug', 'gruppen-und-veranstaltungen')->where('locale', 'de')->first();

        if (! $seite || $seite->blocks()->where('typ', 'kalender')->exists()) {
            return;
        }

        $bloecke = $seite->blocks()->orderBy('position')->get()->values();
        $einleitung = $bloecke->search(fn ($b) => $b->typ === 'text');
        $position = 0;

        foreach ($bloecke as $i => $block) {
            $block->update(['position' => $position++]);

            if ($i === $einleitung) {
                $seite->blocks()->create([
                    'typ' => 'kalender',
                    'position' => $position++,
                    'data' => ['titel' => 'Termine', 'neben_text' => true],
                ]);
            }
        }
    }
}
