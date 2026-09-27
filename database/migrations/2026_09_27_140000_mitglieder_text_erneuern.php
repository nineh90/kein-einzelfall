<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Startseite, Abschnitt „Mitglieder“: neuer Text von Taddi (KEV-55). Der
 * Leitsatz „Werde Teil unseres Netzwerks!“ und der Knopf bleiben.
 *
 * Nur wenn noch der alte Satz allein dasteht. Hat der Verein den Abschnitt
 * im Panel schon selbst geändert, bleibt er unangetastet. Die englische
 * Fassung ist maschinell übersetzt und ungeprüft.
 */
return new class extends Migration
{
    private const FASSUNGEN = [
        'de' => [
            'alt' => 'Jede Mitgliedschaft stärkt unsere Arbeit. Mit jeder Mitgliedschaft wächst '
                .'unsere Chance auf Veränderung.',
            'neu' => 'Du fühlst Dich mit unserer Vision verbunden? Mit Deiner Mitgliedschaft '
                .'kannst Du zeigen, dass Du hinter KE!N EINZELFALL und unserer Arbeit '
                .'stehst. Wie viel Du Dich darüber hinaus einbringen möchtest, entscheidest '
                .'Du ganz für Dich – eine Mitgliedschaft braucht kein aktives Engagement. '
                .'Dein Mitgliedsbeitrag hilft uns gleichzeitig, unsere Arbeit verlässlich zu '
                .'finanzieren, Angebote kostenfrei zu halten und neue Ideen möglich zu machen.',
        ],
        'en' => [
            'alt' => 'Every membership strengthens our work. With every membership our chance '
                .'for change grows.',
            'neu' => 'You feel connected to our vision? With your membership you can show that '
                .'you stand behind KE!N EINZELFALL and our work. How much more you want to get '
                .'involved is entirely up to you – a membership does not require active '
                .'engagement. At the same time, your membership fee helps us to finance our work '
                .'reliably, keep our offers free of charge and make new ideas possible.',
        ],
    ];

    public function up(): void
    {
        $this->tauschen('alt', 'neu');
    }

    public function down(): void
    {
        $this->tauschen('neu', 'alt');
    }

    private function tauschen(string $von, string $nach): void
    {
        // Der Mitglieder-Abschnitt ist der mit dem Knopf zur Mitgliedschaft,
        // am Titel ließe er sich nicht finden (je Sprache anders).
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->get() as $seite) {
            $seite->blocks()
                ->where('typ', 'text')
                ->get()
                ->filter(fn ($block) => ($block->data['cta']['url'] ?? null) === '/mitgliedschaft')
                ->each(function ($block) use ($von, $nach) {
                    foreach (self::FASSUNGEN as $f) {
                        if (($block->data['absaetze'] ?? null) === [$f[$von]]) {
                            $block->update(['data' => array_replace($block->data, ['absaetze' => [$f[$nach]]])]);

                            return;
                        }
                    }
                });
        }
    }
};
