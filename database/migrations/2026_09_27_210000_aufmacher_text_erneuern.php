<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Startseite, Aufmacher: neuer Text von Taddi (KEV-43). Die Überschrift
 * bleibt, nur ihre Linie ist weg, und die steht in der CSS.
 *
 * Nur wo noch der alte Text steht. Die englische Fassung ist maschinell
 * übersetzt und ungeprüft.
 */
return new class extends Migration
{
    private const FASSUNGEN = [
        'de' => [
            'Wir schaffen eine Austausch – und Informationsplattform für Opfer und Mit-Opfer, '
                .'Angehörige, Interessierte und Fachpersonen. Ein zentrales Netzwerk aus Expertise im '
                .'Betroffenenkontext, Austausch auf Augenhöhe. Wir leisten Aufklärung und geben '
                .'Betroffenen eine Stimme. Für mehr Sichtbarkeit und Gehör.',
            'Du bist auf der Informations-, Austausch- und Selbstwirksamkeitsplattform von KE!N '
                .'EINZELFALL e.V. Ein zentrales Netzwerk aus Fach- und Betroffenenexpertise auf '
                .'Augenhöhe. Für Opfer und Mit-Opfer, Angehörige, Interessierte und Fachpersonen. '
                .'Für mehr Sichtbarkeit, Gehör und Unterstützung!',
        ],
        'en' => [
            'We are building a platform for exchange and information for victims and co-victims, '
                .'relatives, interested people and professionals. A central network of expertise '
                .'grounded in lived experience, with exchange on equal terms. We provide education '
                .'and give those affected a voice. For more visibility and being heard.',
            'You are on the information, exchange and self-efficacy platform of KE!N EINZELFALL '
                .'e.V. A central network of professional and lived-experience expertise on equal '
                .'terms. For victims and co-victims, relatives, interested people and professionals. '
                .'For more visibility, being heard and support!',
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
            foreach ($seite->blocks()->where('typ', 'hero')->get() as $block) {
                foreach (self::FASSUNGEN as $f) {
                    if (($block->data['text'] ?? null) === $f[$von]) {
                        $block->update(['data' => array_replace($block->data, ['text' => $f[$nach]])]);
                    }
                }
            }
        }
    }
};
