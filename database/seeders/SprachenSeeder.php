<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

/**
 * Der Startbestand an Sprachen: Deutsch und Englisch.
 *
 * Weitere legt der Verein selbst im Panel an — deshalb ist das hier nur der
 * Startbestand und keine abschliessende Liste. `updateOrCreate` auf `code`,
 * damit ein erneuter Lauf gepflegte Angaben nicht überschreibt, sondern
 * angleicht.
 *
 * Englisch steht bewusst auf `aktiv = false`: Solange keine Übersetzung
 * vorliegt, soll niemand auf einer leeren Sprachfassung landen. Der Verein
 * schaltet es frei, wenn die Inhalte da sind.
 *
 * Russisch war bis zum 19.09.2026 die dritte Sprache — als maschinell
 * übersetzte Vorführung, die niemand gegenlesen konnte. Kevin hat sie
 * gestrichen; die Migration `russisch_entfernen` räumt sie auf bestehenden
 * Installationen ab. Kommt sie einmal wirklich, legt der Verein sie im Panel
 * an — dann mit Texten, die jemand geprüft hat.
 */
class SprachenSeeder extends Seeder
{
    public function run(): void
    {
        $sprachen = [
            [
                'code' => 'de',
                'label' => 'Deutsch',
                'label_deutsch' => 'Deutsch',
                'richtung' => 'ltr',
                'aktiv' => true,
                'position' => 0,
                'ist_standard' => true,
                'fallback_code' => null,
            ],
            [
                'code' => 'en',
                'label' => 'English',
                'label_deutsch' => 'Englisch',
                'richtung' => 'ltr',
                'aktiv' => false,
                'position' => 1,
                'ist_standard' => false,
                'fallback_code' => 'de',
            ],
        ];

        foreach ($sprachen as $sprache) {
            Language::updateOrCreate(['code' => $sprache['code']], $sprache);
        }

        Language::memoLeeren();
    }
}
