<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Teamseite: Die Überleitung vor dem Team („Darüber hinaus gibt es viele
 * Menschen …“) wird ein Absatz statt zwei, wie Taddi sie vorgegeben hat
 * (KEV-66). Der Wortlaut bleibt. Mit zwei Absätzen setzte der Baustein den
 * ersten dunkler und grösser als Einstieg, der Block wirkte uneinheitlich.
 *
 * Dazu die Überschrift „Team“, damit der Abschnitt nicht namenlos zwischen
 * den Karten steht.
 *
 * Nur wo noch genau die zwei alten Absätze stehen.
 */
return new class extends Migration
{
    private const ERSTER = 'Darüber hinaus gibt es viele Menschen, die uns an den unterschiedlichsten '
        .'Punkten unterstützen und den Verein mitgestalten.';

    private const ZWEITER = 'Ohne die Gründungsmitglieder, die zum Teil auch unsere Landesstellen '
        .'vertreten, würde es unseren Verein nicht geben. Sie sorgen – zusammen mit weiteren '
        .'Ehrenamtlichen – zum Beispiel in Interessensvertretungen oder Arbeitsgruppen für die '
        .'Sichtbarkeit des Vereins und unserer Anliegen.';

    public function up(): void
    {
        $this->tauschen([self::ERSTER, self::ZWEITER], [self::ERSTER.' '.self::ZWEITER], 'Team');
    }

    public function down(): void
    {
        $this->tauschen([self::ERSTER.' '.self::ZWEITER], [self::ERSTER, self::ZWEITER], null);
    }

    private function tauschen(array $von, array $nach, ?string $titel): void
    {
        $seiten = Page::where('slug', 'ueber-uns-vorstand-und-team')->get();

        foreach ($seiten as $seite) {
            foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
                if (($block->data['absaetze'] ?? null) !== $von) {
                    continue;
                }

                // Titel vor die Absätze, wie das Panel die Felder speichert.
                $data = array_diff_key($block->data, ['titel' => true, 'absaetze' => true]);
                $data = ($titel ? ['titel' => $titel] : []) + ['absaetze' => $nach] + $data;
                $block->update(['data' => $data]);
            }
        }
    }
};
