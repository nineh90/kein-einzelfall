<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * Landesstellen mit Taddis Text (KEV-104).
 *
 * Die Seite lag seit KEV-78 als leerer Entwurf mit Titelbild bereit. Jetzt
 * wird sie gefüllt, veröffentlicht und steht im Menü „Kontakt“ hinter
 * „Kontakt“: Taddi hat keinen Platz genannt, und die Kontaktseite verspricht
 * im Bild schon „Alle Ansprechpartner, Landesstellen und Zuständigkeiten“.
 *
 * Je Landesstelle ein Abschnitt: Bundesland als Überzeile, „Landesstelle …“
 * als Überschrift, so wie bei Taddi. Absätze von uns gesetzt. Der erste Satz
 * („nicht nur an einem Ort zuhause“) ist die Unterzeile auf dem Bild und fällt
 * deshalb im Text darunter weg (page.blade.php).
 *
 * Abweichung: „Elke Redeker“ statt „Reedeker“ (Kevin, 07.10.2026; so auch in
 * der Adresse redeker@ auf der Kontaktseite).
 *
 * Füllt nur, solange die Seite noch der leere Entwurf ist.
 */
class LandesstellenSeeder extends Seeder
{
    public const SLUG = 'landesstellen';

    public const UNTERZEILE = 'KE!N EINZELFALL ist nicht nur an einem Ort zuhause.';

    public const META_DESCRIPTION = 'Die Landesstellen von KE!N EINZELFALL e.V. in Bayern, Berlin, Hamburg, '
        .'Sachsen-Anhalt und Schleswig-Holstein: regionale Ansprechpartner und Kontakt.';

    /** @return list<array<string, mixed>> */
    public static function abschnitte(): array
    {
        $stelle = fn (string $land, array $absaetze, string $adresse) => [
            'eyebrow' => $land,
            'titel' => 'Landesstelle '.$land,
            'absaetze' => [...$absaetze, 'Kontakt der Landesstelle: '.$adresse],
        ];

        return [
            [
                'titel' => 'Unsere Landesstellen',
                'absaetze' => [
                    self::UNTERZEILE,
                    'Mit unseren Landesstellen möchten wir näher an den Menschen vor Ort sein, regionale Strukturen '
                    .'besser kennen und Ansprechpartner schaffen, die wissen, welche Themen und Herausforderungen im '
                    .'jeweiligen Bundesland eine Rolle spielen.',
                    'Unsere Landesstellen bringen Erfahrungen, Kontakte und regionale Perspektiven in die Arbeit von '
                    .'KE!N EINZELFALL ein und helfen dabei, Informationen, Austausch und Unterstützung näher an die '
                    .'Menschen zu bringen.',
                    'Hier findest du unsere Landesstellen und die jeweiligen Kontaktmöglichkeiten.',
                ],
            ],
            $stelle('Bayern', [
                'Die Landesstelle Bayern wird von Franziska Künstler, 2. Vorsitzende von KE!N EINZELFALL e.V., '
                .'geleitet. Mit viel Erfahrung, einem offenen Ohr und einem Blick für die Besonderheiten vor Ort '
                .'begleitet sie den Aufbau und die Weiterentwicklung unserer Arbeit in Bayern.',
                'Dabei geht es vor allem darum, Menschen miteinander zu verbinden, regionale Wege sichtbar zu machen '
                .'und KE!N EINZELFALL auch vor Ort weiter wachsen zu lassen.',
            ], 'LS-Bayern@kein-einzelfall.de'),
            $stelle('Berlin', [
                'Unsere Landesstelle Berlin wird von Nicole Khalil, Gründungsmitglied von KE!N EINZELFALL e.V., '
                .'geleitet. Sie vertritt KE!N EINZELFALL außerdem im Landesbeirat für Menschen mit Behinderung in '
                .'Berlin und bringt dort Erfahrungen und Perspektiven aus unserer Arbeit ein.',
                'Damit verbindet die Landesstelle Berlin Vereinsarbeit vor Ort mit direkter Beteiligung an wichtigen '
                .'Themen rund um Teilhabe und Barrierefreiheit.',
            ], 'LS-Berlin@kein-einzelfall.de'),
            $stelle('Hamburg', [
                'Unsere Landesstelle Hamburg wird von Tatjana Belmar, 1. Vorsitzende von KE!N EINZELFALL e.V., '
                .'geleitet. Hamburg ist zugleich der Hauptsitz von KE!N EINZELFALL e.V. und der Ort, an dem der '
                .'Verein im Vereinsregister eingetragen ist.',
                'Von hier aus laufen viele zentrale Fäden des Vereins zusammen – gleichzeitig soll auch die Arbeit '
                .'vor Ort in Hamburg weiter sichtbar, erreichbar und vernetzt sein.',
            ], 'LS-Hamburg@kein-einzelfall.de'),
            $stelle('Sachsen-Anhalt', [
                'Unsere Landesstelle Sachsen-Anhalt wird von Elke Redeker, Gründungsmitglied von KE!N EINZELFALL '
                .'e.V., geleitet. Als Gründungsmitglied begleitet sie den Verein seit seinen Anfängen und bringt '
                .'ihre Erfahrung in den Aufbau der Landesstelle ein.',
                'In Sachsen-Anhalt soll so Schritt für Schritt eine verlässliche regionale Anlaufstelle entstehen, '
                .'die KE!N EINZELFALL vor Ort sichtbar macht und weiter vernetzt.',
            ], 'LS-Sachsen-Anhalt@kein-einzelfall.de'),
            $stelle('Schleswig-Holstein', [
                'Unsere Landesstelle Schleswig-Holstein wird von Stefanie Posorske-Gerundt, Gründungsmitglied von '
                .'KE!N EINZELFALL e.V., geleitet. Als Gründungsmitglied kennt sie die Entwicklung des Vereins von '
                .'Anfang an und bringt diese Erfahrung in die Arbeit der Landesstelle ein.',
                'In Schleswig-Holstein liegt ihr besonders am Herzen, Strukturen vor Ort mitzugestalten, Kontakte '
                .'aufzubauen und die Arbeit von KE!N EINZELFALL regional weiter zu verankern.',
            ], 'LS-Schleswig-Holstein@kein-einzelfall.de'),
        ];
    }

    public function run(): void
    {
        self::fuellen();
    }

    public static function fuellen(): void
    {
        $seite = Page::firstOrCreate(
            ['slug' => self::SLUG, 'locale' => 'de'],
            ['titel' => 'Landesstellen', 'meta_title' => 'Landesstellen - Kein Einzelfall e.V.'],
        );

        $leer = $seite->blocks()->get()->every(fn ($b) => $b->typ === 'text' && blank($b->data));

        if (! $leer) {
            return;
        }

        $seite->blocks()->delete();
        foreach (self::abschnitte() as $position => $data) {
            $seite->blocks()->create(['typ' => 'text', 'position' => $position, 'data' => $data]);
        }

        $seite->forceFill([
            'meta_description' => $seite->meta_description ?: self::META_DESCRIPTION,
            'published_at' => $seite->published_at ?? now(),
        ])->save();

        // Bild und Unterzeile, nur wo noch leer.
        Titelbilder::setzen();
    }
}
