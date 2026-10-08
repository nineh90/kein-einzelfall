<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Satzung: neuer Text von Taddi im ersten Abschnitt (KEV-88), dazu eine
 * passende Beschreibung für Suchmaschinen. Beides nur, wo noch der Wortlaut
 * der Altseite steht. Danach die englische Fassung neu, wie bei allen Seiten
 * seit 08.10.2026.
 */
return new class extends Migration
{
    private const TITEL = 'Die Satzung des KE!N EINZELFALL e.V.';

    private const ALTE_BESCHREIBUNG = 'Die Satzung des KE!N EINZELFALL e.V. Unser Fundament: Werte, Ziele, '
        .'Verantwortung & Miteinander.';

    /** Der letzte Absatz mit und ohne das Leerzeichen, das die Textpflege nachgetragen hat. */
    private const ALT_ENDE = 'Mit der Veröffentlichung unserer Satzung möchten wir Transparenz schaffen und zeigen, wie '
        .'wir Opfer und Mitopfer stärken und gesellschaftliche Veränderung vorantreiben.%sWer verstehen möchte, wie '
        .'wir arbeiten und wofür wir stehen, findet hier alle Informationen – klar, verbindlich und offen für alle.';

    private const ALT = [
        'Unser Fundament: Werte, Ziele, Verantwortung & Miteinander.',
        'Wir freuen uns, euch auf dieser Seite unsere Satzung vorstellen zu können. Was wir tun, warum wir es tun '
        .'und wie wir gemeinsam Verantwortung übernehmen – all das steht in unserer Satzung. Sie ist das Fundament '
        .'von KE!N EINZELFALL e.V. und spiegelt unsere Werte, Ziele und unser Selbstverständnis als Verein wider. '
        .'Die Satzung regelt nicht nur formale Abläufe, sondern ist Ausdruck unserer politischen Haltung und '
        .'unseres solidarischen Miteinanders.',
    ];

    public function up(): void
    {
        $neu = AltseiteSeeder::NEUE_TEXTE['satzung'][self::TITEL];

        foreach ([' ', ''] as $leer) {
            $this->tauschen([...self::ALT, sprintf(self::ALT_ENDE, $leer)], $neu);
        }

        Page::where('slug', 'satzung')->where('locale', 'de')->where('meta_description', self::ALTE_BESCHREIBUNG)
            ->update(['meta_description' => AltseiteSeeder::NEUE_BESCHREIBUNGEN['satzung']]);

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        $this->tauschen(AltseiteSeeder::NEUE_TEXTE['satzung'][self::TITEL], [...self::ALT, sprintf(self::ALT_ENDE, ' ')]);

        Page::where('slug', 'satzung')->where('locale', 'de')
            ->where('meta_description', AltseiteSeeder::NEUE_BESCHREIBUNGEN['satzung'])
            ->update(['meta_description' => self::ALTE_BESCHREIBUNG]);
    }

    private function tauschen(array $von, array $nach): void
    {
        foreach (Page::where('slug', 'satzung')->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
                if (($block->data['titel'] ?? null) === self::TITEL && ($block->data['absaetze'] ?? null) === $von) {
                    $block->update(['data' => array_replace($block->data, ['absaetze' => $nach])]);
                }
            }
        }
    }
};
