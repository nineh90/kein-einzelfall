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

    public const ABSCHNITT = [
        'titel' => 'Assoziiertes Fachgremium – UKFB',
        'absaetze' => [
            'Das Unabhängige Kuratorium für Betroffenenexpertise (UKFB) ist als eigenständiges und unabhängiges '
                .'Fachgremium an KE!N EINZELFALL e.V. assoziiert.',
            'KE!N EINZELFALL e.V. nimmt keinen Einfluss auf die inhaltliche Arbeit, Positionen, Entscheidungen '
                .'oder Bewertungen des UKFB. Das Kuratorium arbeitet unabhängig und in eigener Verantwortung.',
            'Die Grundlagen dieser Zusammenarbeit sind in einer Assoziationsvereinbarung transparent geregelt.',
            'Kontakt: kontakt@ukfb.org',
        ],
        'cta' => ['label' => 'Zur Website des UKFB', 'url' => self::HOMEPAGE, 'variant' => 'primary'],
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
        $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => self::ABSCHNITT]);

        $seite->forceFill([
            'meta_description' => $seite->meta_description ?: self::META_DESCRIPTION,
            'published_at' => $seite->published_at ?? now(),
        ])->save();

        // Bild und Unterzeile, nur wo noch leer.
        Titelbilder::setzen();
    }
}
