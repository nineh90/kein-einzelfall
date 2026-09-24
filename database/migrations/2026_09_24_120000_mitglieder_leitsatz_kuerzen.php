<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Startseite, Abschnitt „Mitglieder“: der lange zweite Absatz wird zum
 * handschriftlichen Leitsatz, wie „Opferhilfe für soziale Gerechtigkeit!“
 * nebenan bei „Vereinsarbeit“ (KEV-32).
 *
 * Nur wenn der Absatz noch wörtlich so dasteht. Hat der Verein den Abschnitt
 * im Panel schon selbst geändert, bleibt er unangetastet.
 */
return new class extends Migration
{
    private const FASSUNGEN = [
        'de' => [
            'alt' => 'Sei auch Du Teil unseres ständig wachsenden Netzwerks und unterstütze '
                .'unsere Vision, indem Du Mitglied wirst.',
            'neu' => 'Werde Teil unseres Netzwerks!',
        ],
        'en' => [
            'alt' => 'Become part of our constantly growing network too, and support our '
                .'vision by becoming a member.',
            'neu' => 'Become part of our network!',
        ],
    ];

    public function up(): void
    {
        foreach (self::FASSUNGEN as $locale => $text) {
            $this->tauschen($locale, function (array $data) use ($text) {
                $absaetze = $data['absaetze'] ?? [];

                if (($data['hand'] ?? null) || end($absaetze) !== $text['alt']) {
                    return null;
                }

                array_pop($absaetze);
                $data['absaetze'] = $absaetze;
                $data['hand'] = $text['neu'];

                return $data;
            });
        }
    }

    public function down(): void
    {
        foreach (self::FASSUNGEN as $locale => $text) {
            $this->tauschen($locale, function (array $data) use ($text) {
                if (($data['hand'] ?? null) !== $text['neu']) {
                    return null;
                }

                unset($data['hand']);
                $data['absaetze'][] = $text['alt'];

                return $data;
            });
        }
    }

    /** @param  callable(array): ?array  $aendern  null = nichts tun */
    private function tauschen(string $locale, callable $aendern): void
    {
        $seite = Page::where('slug', 'startseite')->where('locale', $locale)->first();

        // Der Mitglieder-Abschnitt ist der mit dem Knopf zur Mitgliedschaft —
        // am Titel ließe er sich nicht finden, der ist je Sprache anders.
        $seite?->blocks()
            ->where('typ', 'text')
            ->get()
            ->filter(fn ($block) => ($block->data['cta']['url'] ?? null) === '/mitgliedschaft')
            ->each(function ($block) use ($aendern) {
                $neu = $aendern($block->data);

                if ($neu !== null) {
                    $block->update(['data' => $neu]);
                }
            });
    }
};
