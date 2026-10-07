<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Beschwerdemanagement mit Taddis Text (KEV-98).
 *
 * Die Seite lag seit August als leerer Entwurf bereit (NeueBereicheSeeder),
 * seit KEV-80 mit Titelbild. Jetzt bekommt sie ihren Inhalt: zwei Spalten,
 * links Kritik von außen, rechts Beschwerden über den Verein, darunter ein
 * gemeinsames Formular, in dem man den Weg wählt. Sie wird veröffentlicht und steht im Menü „Verein“ an
 * Position 5 (config/navigation.php).
 *
 * Abweichung von Taddis Text: „beschwedemanagement@“ zu
 * „beschwerdemanagement@“ korrigiert. Die Absätze sind von uns gesetzt,
 * ihr Text kam als ein Block.
 *
 * Füllt nur, solange die Seite noch der leere Entwurf ist. Hat der Verein
 * sie im Panel schon bearbeitet, bleibt sie, wie sie ist.
 */
class BeschwerdemanagementSeeder extends Seeder
{
    public const SPALTEN = [
        [
            'titel' => 'Externe Kritik',
            'absaetze' => [
                'Du kommst mit einem Anliegen nicht weiter, bist mit einer Bearbeitung oder Entscheidung unzufrieden oder fühlst dich nicht ernst genommen? Dann kannst du dich an uns wenden.',
                'Wir schauen uns gemeinsam mit dir an, worum es geht, ordnen den Sachverhalt und prüfen, welche nächsten Schritte möglich sind. Dabei möchten wir möglichst unkompliziert und transparent arbeiten.',
                'Je nach Anliegen können wir Informationen verständlich machen, Möglichkeiten aufzeigen, beim weiteren Vorgehen unterstützen oder passende Stellen und Kontakte einbeziehen.',
                'Wichtig ist uns: Deine Kritik darf gehört werden.',
                'Wenn du Unterstützung brauchst, melde dich bei uns: kritik@kein-einzelfall.de',
            ],
            'hand' => 'Du bist nicht allein. Gemeinsam KE!N EINZELFALL!',
            // Knopf und Auswahl im Formular sind von uns, nicht von Taddi.
            'knopf' => 'Kritik schreiben',
            // Landet wie jede Anfrage verschlüsselt im Verwaltungsbereich.
            'art' => 'anfrage',
        ],
        [
            'titel' => 'Interne Beschwerde',
            'absaetze' => [
                'Du hast ein Problem mit unserem Verein, unserem Umgang oder einer Entscheidung von KE!N EINZELFALL e.V.? Dann sag uns bitte Bescheid.',
                'Kritik, Beschwerden und Hinweise helfen uns, Verantwortung zu übernehmen, Fehler zu erkennen und unsere Arbeit weiterzuentwickeln. Du musst dein Anliegen dabei nicht perfekt formulieren oder schon eine Lösung haben.',
                'Deine Nachricht an beschwerdemanagement@kein-einzelfall.de geht an eine unabhängige Ombudsstelle, die dein Anliegen entgegennimmt und sich um die weitere Bearbeitung kümmert.',
                'Wichtig ist uns, dass Kritik möglich ist – auch dann, wenn sie unangenehm ist. Wenn etwas bei uns nicht gut läuft, möchten auch wir davon erfahren.',
            ],
            'knopf' => 'Beschwerde schreiben',
            // Geht per E-Mail an die Ombudsstelle, nicht in den
            // Verwaltungsbereich. Siehe BeschwerdeController.
            'art' => 'ombudsstelle',
        ],
    ];

    public const META_DESCRIPTION = 'Kritik und Beschwerden: Hier erreichst du KE!N EINZELFALL e.V. '
        .'mit deinem Anliegen, und mit einer Beschwerde über den Verein die unabhängige Ombudsstelle.';

    public function run(): void
    {
        self::fuellen();
    }

    public static function fuellen(): void
    {
        $seite = Page::firstOrCreate(
            ['slug' => 'beschwerdemanagement', 'locale' => 'de'],
            ['titel' => 'Beschwerdemanagement', 'meta_title' => 'Beschwerdemanagement - Kein Einzelfall e.V.'],
        );

        // Der Entwurf aus NeueBereicheSeeder: höchstens ein Textbaustein ohne
        // Inhalt. Alles andere hat jemand gepflegt.
        $bloecke = $seite->blocks()->get();
        $leer = $bloecke->every(fn ($b) => $b->typ === 'text' && blank($b->data));

        if (! $leer) {
            return;
        }

        $seite->blocks()->delete();
        $seite->blocks()->create([
            'typ' => 'formular_spalten',
            'position' => 0,
            'data' => ['spalten' => self::SPALTEN],
        ]);

        $seite->forceFill([
            'meta_description' => $seite->meta_description ?: self::META_DESCRIPTION,
            'published_at' => $seite->published_at ?? now(),
        ])->save();
    }
}
