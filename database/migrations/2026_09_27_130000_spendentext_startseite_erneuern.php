<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Startseite, Abschnitt „Jetzt spenden“: neuer Text von Taddi mit Leitsatz
 * in Handschrift, und die Dachzeile „Spenden“ fällt weg (KEV-56). Sie hatte
 * einen eigenen grünen Strich, direkt darunter stand der Strich über der
 * Überschrift. Der Text war bisher derselbe wie auf der Einstiegskarte
 * „Spenden“ weiter oben.
 *
 * Nur wo noch die alte Einleitung steht. Hat der Verein den Abschnitt im
 * Panel schon selbst geändert, bleibt er unangetastet. Die englische Fassung
 * ist maschinell übersetzt und ungeprüft, wie das übrige Wörterbuch.
 */
return new class extends Migration
{
    private const FASSUNGEN = [
        'de' => [
            'dach' => 'Spenden',
            'alt' => 'Mit Deiner Spende hilfst Du uns, kostenfreies Wissen und Aufklärung zu '
                .'leisten, Sichtbarkeit und Gehör zu schaffen, sowie eine Informationsplattform '
                .'aufzustellen und ein Netzwerk zu bilden.',
            'hand' => 'Gute Ideen brauchen Rückenwind.',
            'neu' => 'Deine Spende hilft uns, Projekte umzusetzen, Wissen kostenfrei zugänglich '
                .'zu machen, aufzuklären, Stimmen hörbar zu machen und KE!N EINZELFALL als '
                .'Informations-, Austausch- und Selbstwirksamkeitsplattform weiter wachsen zu lassen.',
        ],
        'en' => [
            'dach' => 'Donate',
            'alt' => 'With your donation you help us to provide free knowledge and education, to '
                .'create visibility and being heard, and to build an information platform and a network.',
            'hand' => 'Good ideas need a tailwind.',
            'neu' => 'Your donation helps us to carry out projects, make knowledge freely '
                .'accessible, raise awareness, make voices heard and let KE!N EINZELFALL keep '
                .'growing as a platform for information, exchange and self-efficacy.',
        ],
    ];

    public function up(): void
    {
        $this->jeBaustein(function (array $data) {
            foreach (self::FASSUNGEN as $f) {
                if (($data['text'] ?? null) !== $f['alt']) {
                    continue;
                }

                if (($data['eyebrow'] ?? null) === $f['dach']) {
                    unset($data['eyebrow']);
                }

                $data['text'] = $f['neu'];
                $data['hand'] ??= $f['hand'];

                return $data;
            }

            return null;
        });
    }

    public function down(): void
    {
        $this->jeBaustein(function (array $data) {
            foreach (self::FASSUNGEN as $f) {
                if (($data['text'] ?? null) !== $f['neu']) {
                    continue;
                }

                $data['text'] = $f['alt'];
                $data['eyebrow'] ??= $f['dach'];

                if (($data['hand'] ?? null) === $f['hand']) {
                    unset($data['hand']);
                }

                return $data;
            }

            return null;
        });
    }

    /** @param  callable(array): ?array  $aendern  null = nichts tun */
    private function jeBaustein(callable $aendern): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'donation_options')->get() as $block) {
                $neu = $aendern($block->data);

                if ($neu !== null) {
                    $block->update(['data' => $neu]);
                }
            }
        }
    }
};
