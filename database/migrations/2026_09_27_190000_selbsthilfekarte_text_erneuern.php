<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Startseite, „Was wir gemeinsam bewegen“: neuer Text der Karte
 * „Selbsthilfegruppen“ von Taddi, der von ihr markierte Satz fett (KEV-48).
 *
 * Nur wo noch der alte Text steht. Die englische Fassung ist maschinell
 * übersetzt und ungeprüft.
 */
return new class extends Migration
{
    private const FASSUNGEN = [
        'de' => [
            'Der Austausch in unseren Selbsthilfegruppen soll Dir genau da helfen, wo Du Hilfe '
                .'benötigst, und er soll Dir aufzeigen, dass Du endlich nicht mehr alleine bist, denn '
                .'wir sind KE!N EINZELFALL! Die Selbsthilfegruppen sind kostenfrei und nicht an eine '
                .'Mitgliedschaft gebunden.',
            '*Manchmal tut es gut, Menschen zu treffen, die verstehen, ohne dass Du viel erklären '
                .'musst.* In unseren Selbsthilfegruppen kannst Du Dich austauschen, Erfahrungen teilen '
                .'und neue Perspektiven kennenlernen. Du entscheidest selbst, ob Du erzählen, mitreden '
                .'oder erstmal einfach nur zuhören möchtest.',
        ],
        'en' => [
            'The exchange in our self-help groups is meant to help you exactly where you need it, '
                .'and to show you that you are finally no longer alone, because we are NOT A SINGLE '
                .'CASE! The self-help groups are free of charge and not tied to any membership.',
            '*Sometimes it helps to meet people who understand without you having to explain much.* '
                .'In our self-help groups you can talk with others, share experiences and discover new '
                .'perspectives. You decide for yourself whether you want to tell your story, join in or '
                .'simply listen for now.',
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
                        if (($karte['url'] ?? null) === '/selbsthilfegruppen' && ($karte['text'] ?? null) === $f[$von]) {
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
