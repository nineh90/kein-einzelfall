<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * Gremium UKFB mit Taddis Text (KEV-105).
 *
 * Die Seite lag seit KEV-81 als leerer Entwurf mit Titelbild bereit. Jetzt
 * wird sie gefüllt, veröffentlicht und steht am Ende des Menüs „Verein“.
 *
 * Abweichung von Taddis Text: Sie schrieb „kontakt@ufb.org“ und „ukf.org“.
 * ufb.org steht zum Verkauf, ukf.org antwortet nicht. Die Seite des
 * Kuratoriums ist ukfb.org und nennt selbst kontakt@ukfb.org (geprüft am
 * 07.10.2026). Absätze von uns gesetzt.
 *
 * Füllt nur, solange die Seite noch der leere Entwurf ist.
 */
class GremiumUkfbSeeder extends Seeder
{
    public const SLUG = 'gremium-ukfb';

    public const HOMEPAGE = 'https://ukfb.org';

    /** Logo des Kuratoriums, aus Taddis Drive („Bilder“), KEV-221. */
    public const LOGO = '/img/ukfb/logo-ukfb.webp';

    /** Überschrift bis KEV-221, woran der unveränderte Abschnitt zu erkennen ist. */
    public const ALTE_UEBERSCHRIFT = 'Assoziiertes Fachgremium – UKFB';

    public const ABSCHNITT = [
        // KEV-221: Taddi wollte UKFB vorn, rechts daneben das Logo.
        'titel' => 'UKFB – Assoziiertes Fachgremium',
        'absaetze' => [
            'Das Unabhängige Kuratorium für Betroffenenexpertise (UKFB) ist als eigenständiges und unabhängiges '
                .'Fachgremium an KE!N EINZELFALL e.V. assoziiert.',
            'KE!N EINZELFALL e.V. nimmt keinen Einfluss auf die inhaltliche Arbeit, Positionen, Entscheidungen '
                .'oder Bewertungen des UKFB. Das Kuratorium arbeitet unabhängig und in eigener Verantwortung.',
            'Die Grundlagen dieser Zusammenarbeit sind in einer Assoziationsvereinbarung transparent geregelt.',
            'Kontakt: kontakt@ukfb.org',
        ],
        'cta' => ['label' => 'Zur Website des UKFB', 'url' => self::HOMEPAGE, 'variant' => 'primary'],
        'bild' => self::LOGO,
        'bild_alt' => 'Logo des UKFB: neun Menschen an einem Tisch, darunter UKFB, Unabhängiges Kuratorium für Betroffenenexpertise',
        'bild_seite' => 'rechts',
    ];

    public const META_DESCRIPTION = 'Das Unabhängige Kuratorium für Betroffenenexpertise (UKFB) ist als '
        .'eigenständiges Fachgremium an KE!N EINZELFALL e.V. assoziiert und arbeitet unabhängig.';

    public function run(): void
    {
        self::fuellen();
    }

    public static function fuellen(): void
    {
        $seite = Page::firstOrCreate(
            ['slug' => self::SLUG, 'locale' => 'de'],
            ['titel' => 'Gremium UKFB', 'meta_title' => 'Gremium UKFB - Kein Einzelfall e.V.'],
        );

        $leer = $seite->blocks()->get()->every(fn ($b) => $b->typ === 'text' && blank($b->data));

        if (! $leer) {
            return;
        }

        $seite->blocks()->delete();
        $seite->blocks()->create(['typ' => 'text_media', 'position' => 0, 'data' => self::ABSCHNITT]);

        $seite->forceFill([
            'meta_description' => $seite->meta_description ?: self::META_DESCRIPTION,
            'published_at' => $seite->published_at ?? now(),
        ])->save();

        // Bild und Unterzeile, nur wo noch leer.
        Titelbilder::setzen();
    }

    /**
     * KEV-221: Der Abschnitt wird „Text mit Bild“, mit dem Logo rechts und
     * „UKFB“ vorn in der Überschrift. Nur wo noch die bisherige Überschrift
     * steht; Text und Knopf bleiben, wie sie sind.
     */
    public static function logoUndUeberschrift(): void
    {
        foreach (Page::where('slug', self::SLUG)->where('locale', 'de')->get() as $seite) {
            $block = $seite->blocks()->where('typ', 'text')->get()
                ->first(fn ($b) => ($b->data['titel'] ?? null) === self::ALTE_UEBERSCHRIFT);

            if (! $block) {
                continue;
            }

            $block->update([
                'typ' => 'text_media',
                'data' => array_replace($block->data, [
                    'titel' => self::ABSCHNITT['titel'],
                    'bild' => self::ABSCHNITT['bild'],
                    'bild_alt' => self::ABSCHNITT['bild_alt'],
                    'bild_seite' => 'rechts',
                ]),
            ]);
        }
    }
}
