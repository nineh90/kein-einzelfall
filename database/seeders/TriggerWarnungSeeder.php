<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Der vorgeschaltete Hinweis auf belastende Inhalte.
 *
 * Ausdrücklicher Wunsch des Vereins aus dem Strukturpapier und noch einmal
 * bestätigt in der Besprechung vom 02.08.2026.
 *
 * ⚠️ Der Wortlaut hier ist ein Vorschlag und gehört bestätigt — er steht als
 * A-Punkt auf der Übergabe-Checkliste. Bewusst nüchtern und beschreibend
 * gehalten: Er sagt, worum es auf dieser Website geht, und trifft keine Aussage
 * im Namen des Vereins. Was der Verein über sich selbst sagt, schreibt der
 * Verein.
 *
 * Abschalten geht ohne Deployment: Seite im Panel auf Entwurf setzen, dann
 * entfällt der Hinweis überall.
 */
class TriggerWarnungSeeder extends Seeder
{
    public function run(): void
    {
        $seite = Page::updateOrCreate(
            [
                'locale' => Language::standardCode(),
                'fassung' => Page::FASSUNG_STANDARD,
                'slug' => Page::TRIGGER_SLUG,
            ],
            [
                'titel' => 'Hinweis zu den Inhalten dieser Website',
                'meta_description' => 'Auf dieser Website geht es um Straftaten, Gewalt und ihre '
                    .'Folgen. Ein Hinweis vorab, und wie du die Seite jederzeit schnell verlässt.',
                /*
                 * Von Suchmaschinen ausgenommen. Der Hinweis steht ohnehin auf
                 * jeder Seite — als eigener Treffer in einer Ergebnisliste wäre
                 * er nur ein Einstieg ins Nichts.
                 */
                'noindex' => true,
                'published_at' => now(),
            ],
        );

        // Der Seeder ist wiederholbar. Ohne dies stünde der Text nach dem
        // zweiten Lauf doppelt im Dialog.
        $seite->blocks()->delete();

        $seite->blocks()->create([
            'typ' => 'text',
            'position' => 0,
            'data' => [
                'absaetze' => [
                    'Auf dieser Website geht es um Straftaten, Gewalt und ihre Folgen. '
                        .'Einzelne Texte, Erfahrungsberichte und Dokumente können belastend '
                        .'sein oder Erinnerungen auslösen.',
                    'Du entscheidest, was du liest und wann. Du kannst jederzeit aufhören, '
                        .'zurückgehen oder diese Seite sofort verlassen — dafür gibt es unten '
                        .'einen Knopf, oben rechts den Notausgang, und du kannst dreimal '
                        .'hintereinander die Taste Esc drücken.',
                    'Wenn du gerade Hilfe brauchst: Die Notfallnummern stehen am Ende jeder '
                        .'Seite und sind rund um die Uhr erreichbar.',
                ],
            ],
        ]);
    }
}
