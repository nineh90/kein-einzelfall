<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Startseite, linke Spalte neben „Mitglieder“ (KEV-54): Titel „Verein“
 * statt „Vereinsarbeit“, neuer Text von Taddi mit fettem ersten Satz und
 * der Leitsatz „Für Sichtbarkeit. Für eine Stimme. Für Unterstützung.“ statt
 * „Opferhilfe für soziale Gerechtigkeit!“.
 *
 * Jedes Feld nur, solange dort noch der alte Wortlaut steht. Was der Verein
 * im Panel schon selbst geändert hat, bleibt. Die englische Fassung ist
 * maschinell übersetzt und ungeprüft.
 */
return new class extends Migration
{
    private const FASSUNGEN = [
        'de' => [
            'titel' => ['Vereinsarbeit', 'Verein'],
            'absatz' => [
                'Der KE!N EINZELFALL e.V. wurde 2024 gegründet – aus einer persönlichen '
                    .'Betroffenheit heraus und mit dem Ziel, von schädigenden Taten betroffene '
                    .'Menschen nicht länger allein zu lassen.',
                '*KE!N EINZELFALL e.V. wurde 2024 aus persönlicher Betroffenheit heraus '
                    .'gegründet.* Aus Erfahrung wurde Wissen und aus Wissen wurde '
                    .'Betroffenenexpertise. Heute schaffen wir Räume für Austausch, teilen '
                    .'Wissen und machen sichtbar, was Betroffene bewegt.',
            ],
            'hand' => ['Opferhilfe für soziale Gerechtigkeit!', 'Für Sichtbarkeit. Für eine Stimme. Für Unterstützung.'],
        ],
        'en' => [
            'titel' => ['Our work', 'The Association'],
            'absatz' => [
                'KE!N EINZELFALL e.V. was founded in 2024 – out of personal experience and with '
                    .'the goal of no longer leaving people affected by harmful acts on their own.',
                '*KE!N EINZELFALL e.V. was founded in 2024 out of personal experience.* Experience '
                    .'turned into knowledge, and knowledge turned into lived-experience expertise. '
                    .'Today we create spaces for exchange, share knowledge and make visible what '
                    .'matters to those affected.',
            ],
            'hand' => ['Victim support for social justice!', 'For visibility. For a voice. For support.'],
        ],
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
            // Der Abschnitt mit dem Knopf zum Verein, am Titel ließe er sich
            // nicht finden (je Sprache anders, und genau der ändert sich hier).
            $bloecke = $seite->blocks()->where('typ', 'text')->get()
                ->filter(fn ($block) => ($block->data['cta']['url'] ?? null) === '/verein');

            foreach ($bloecke as $block) {
                $data = $block->data;

                foreach (self::FASSUNGEN as $f) {
                    if (($data['absaetze'] ?? null) !== [$f['absatz'][$von]]) {
                        continue;
                    }

                    $neu = ['absaetze' => [$f['absatz'][$nach]]];

                    if (($data['titel'] ?? null) === $f['titel'][$von]) {
                        $neu['titel'] = $f['titel'][$nach];
                    }

                    if (($data['hand'] ?? null) === $f['hand'][$von]) {
                        $neu['hand'] = $f['hand'][$nach];
                    }

                    // array_replace: Die Reihenfolge der Felder bleibt, sonst
                    // meldete das Panel beim Speichern eine Änderung.
                    $block->update(['data' => array_replace($data, $neu)]);
                }
            }
        }
    }
};
