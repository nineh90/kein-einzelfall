<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Vereinsseite: Der Textblock „Mehr über:“ fällt weg (KEV-70). Auf der
 * Altseite war er eine Linkliste, bei uns kamen nur die Zeilen ohne Links an.
 * Dieselben Seiten verlinken direkt darunter die Karten „Mehr zu …“.
 *
 * Alle Sprachen: Die Übersetzung ist eine Kopie der deutschen Blöcke. Gelöscht
 * wird nur, was noch genau die fünf alten Zeilen trägt; hat der Verein den
 * Block im Panel umgeschrieben, bleibt er stehen.
 */
return new class extends Migration
{
    private const DE = [
        'titel' => 'Mehr über:',
        'absaetze' => [
            'Unser Team – wer steht hinter unserem Verein?',
            'Unsere Satzung',
            'Istanbul-Konvention',
            'Kinderkodex',
            'Mitgliedschaft – wie kannst du uns als Mitglied unterstützen?',
        ],
    ];

    public function up(): void
    {
        $woerterbuch = json_decode((string) @file_get_contents(database_path('seeders/data/uebersetzungen.json')), true) ?: [];

        foreach (Page::where('slug', 'verein')->get() as $seite) {
            $alt = $this->fassung($woerterbuch, $seite->locale);

            foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
                if (($block->data['titel'] ?? null) === $alt['titel']
                    && ($block->data['absaetze'] ?? null) === $alt['absaetze']) {
                    $block->delete();
                }
            }
        }
    }

    public function down(): void
    {
        // Nicht zurückholen: Die Zeilen waren tote Links, und die Karten
        // darunter decken sie ab.
    }

    /** Titel und Zeilen, wie sie in der jeweiligen Sprache angelegt wurden. */
    private function fassung(array $woerterbuch, string $locale): array
    {
        $tr = fn (string $de) => $woerterbuch[$de][$locale] ?? $de;

        return [
            'titel' => $tr(self::DE['titel']),
            'absaetze' => array_map($tr, self::DE['absaetze']),
        ];
    }
};
