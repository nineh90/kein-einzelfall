<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

/**
 * Die drei beauftragten Sprachen.
 *
 * Weitere legt der Verein selbst im Panel an — deshalb ist das hier nur der
 * Startbestand und keine abschliessende Liste. `updateOrCreate` auf `code`,
 * damit ein erneuter Lauf gepflegte Angaben nicht überschreibt, sondern
 * angleicht.
 *
 * Englisch und Russisch stehen bewusst auf `aktiv = false`: Solange keine
 * Übersetzung vorliegt, soll niemand auf einer leeren Sprachfassung landen.
 * Der Verein schaltet sie frei, wenn die Inhalte da sind.
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
            [
                'code' => 'ru',
                'label' => 'Русский',
                'label_deutsch' => 'Russisch',
                'richtung' => 'ltr',
                'aktiv' => false,
                'position' => 2,
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
