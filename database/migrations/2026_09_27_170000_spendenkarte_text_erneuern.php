<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Startseite, „Unsere Aufgabe“: neuer Text der Karte „Spenden“ von Taddi,
 * die beiden von ihr markierten Sätze fett (KEV-52).
 *
 * Nur wo noch der alte Text steht. Die englische Fassung ist maschinell
 * übersetzt und ungeprüft.
 */
return new class extends Migration
{
    private const FASSUNGEN = [
        'de' => [
            'Mit Deiner Spende hilfst Du uns, kostenfreies Wissen und Aufklärung zu leisten, '
                .'Sichtbarkeit und Gehör zu schaffen, sowie eine Informationsplattform aufzustellen '
                .'und ein Netzwerk zu bilden.',
            '*Deine Spende macht unsere Arbeit möglich.* Sie hilft uns, Informationen und Wissen '
                .'kostenfrei zugänglich zu machen, Selbsthilfe und Austausch zu ermöglichen und '
                .'Projekte umzusetzen, die Betroffenen eine Stimme geben. Danke, dass Du dabei bist. '
                .'*Jeder Beitrag hilft uns, unabhängig zu arbeiten und gemeinsam etwas zu bewegen.*',
        ],
        'en' => [
            'With your donation you help us to provide free knowledge and education, to create '
                .'visibility and being heard, and to build an information platform and a network.',
            '*Your donation makes our work possible.* It helps us to make information and knowledge '
                .'freely accessible, to enable self-help and exchange, and to carry out projects that '
                .'give those affected a voice. Thank you for being part of it. *Every contribution '
                .'helps us to work independently and to make a difference together.*',
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
            foreach ($seite->blocks()->where('typ', 'quick_access')->get() as $block) {
                $data = $block->data;
                $geaendert = false;

                foreach ($data['karten'] ?? [] as $i => $karte) {
                    foreach (self::FASSUNGEN as $f) {
                        if (($karte['url'] ?? null) === '/spenden' && ($karte['text'] ?? null) === $f[$von]) {
                            $data['karten'][$i]['text'] = $f[$nach];
                            $geaendert = true;
                        }
                    }
                }

                if ($geaendert) {
                    $block->update(['data' => $data]);
                }
            }
        }
    }
};
