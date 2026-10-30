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
 * Der Wortlaut ist seit KEV-109 (19.10.2026) von Taddi. Vorher stand hier ein
 * nüchterner Vorschlag von uns, der auf Bestätigung wartete.
 *
 * Abschalten geht ohne Deployment: Seite im Panel auf Entwurf setzen, dann
 * entfällt der Hinweis überall.
 */
class TriggerWarnungSeeder extends Seeder
{
    /*
     * Text von Taddi (KEV-109). Kam am Stück, die Absätze sind von uns. Zwei
     * Korrekturen: „und die oft schwierigen Wege“ statt „, und den oft
     * schwierigen Wegen“ (hängt an „um“), und „Wenn du gerade Hilfe brauchst:“
     * mit Doppelpunkt, sonst fehlt dem Satz das Verb an zweiter Stelle.
     * {darstellung} wird das Symbol des Knopfs (Taddi: „vielleicht kann man
     * den hier grafisch einfügen“).
     *
     * Seit KEV-285 kommt „Einige Inhalte können belastend sein“ vor dem
     * Absatz zum Darstellungs-Knopf, vorher dahinter.
     */
    public const ABSAETZE = [
        'Auf dieser Homepage geht es um die Folgeschäden von physischen, psychischen, digitalen und '
            .'sexuellen Gewalttaten und die oft schwierigen Wege durch Hilfe-, Versorgungs- und '
            .'Entschädigungssysteme.',
        'Einige Inhalte können belastend sein. Du entscheidest, was du wann liest. Du kannst die Seite '
            .'jederzeit über den Button unten rechts, den Notausgang oben rechts oder durch dreimaliges '
            .'Drücken der Esc-Taste verlassen.',
        'Links am Rand findest du einen Button {darstellung}, mit dem du die Darstellung der Seite '
            .'(Schriftgröße/Kontrast etc.) verändern kannst.',
        'Wenn du gerade Hilfe brauchst: Notfallnummern und Hilfsangebote findest du weiter unten. Die '
            .'TelefonSeelsorge (116 123) ist rund um die Uhr erreichbar.',
    ];

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
            'data' => ['absaetze' => self::ABSAETZE],
        ]);
    }
}
