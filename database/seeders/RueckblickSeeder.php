<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Rückblick auf vergangene Veranstaltungen (KEV-142).
 *
 * Die Seite gibt es seit KEV-72, bisher ohne Inhalt. Jede Veranstaltung ist
 * ein Baustein „Text mit Bild“: Überschrift, Taddis Text, ihr Bild. Für die
 * nächste legt der Verein im Panel einfach einen weiteren an.
 *
 * Der erste: „KE!N EINZELFALL im Dialog – Trauma, Bindung und Beziehung“ vom
 * 10. August 2026, mit Taddis Text und ihrem Bild (zwei Holzstühle im
 * Licht, von Kevin geschickt). Absätze von uns gesetzt. Korrigiert: Komma in
 * „Mit den Fragen, die Menschen, die mit den Folgen von Trauma leben,
 * beschäftigen.“ Der Schlusssatz, der Name der Reihe, fett.
 *
 * Mit Inhalt darf die Seite gefunden werden (vorher noindex wie andere leere
 * Seiten). Legt nur an, was fehlt.
 */
class RueckblickSeeder extends Seeder
{
    public const SLUG = 'rueckblick';

    public const META_DESCRIPTION = 'Rückblick auf Veranstaltungen von KE!N EINZELFALL e.V., zuletzt „KE!N '
        .'EINZELFALL im Dialog – Trauma, Bindung und Beziehung“ mit Lutz-Ulrich Besser.';

    public const TRAUMA_TITEL = 'KE!N EINZELFALL im Dialog – Trauma, Bindung und Beziehung';

    public const TRAUMA_BILD = '/img/rueckblick/trauma-bindung-und-beziehung.webp';

    public const TRAUMA_ABSAETZE = [
        'Mit „Trauma, Bindung und Beziehung“ startete am 10. August 2026 unsere Veranstaltungsreihe „KE!N '
            .'EINZELFALL im Dialog – Wissen trifft Erfahrung“. Unser Anliegen war es, fachliches Wissen und '
            .'gelebte Erfahrung miteinander zu verbinden.',
        'Denn Trauma betrifft nicht nur einzelne Erinnerungen oder Symptome. Es kann sich auch auf Vertrauen, '
            .'Nähe, Sicherheit und Beziehungen auswirken – und damit auf Partnerschaften, Familien, Freundschaften '
            .'und den gesamten Alltag.',
        'Als Referenten durften wir Lutz-Ulrich Besser, Facharzt für Psychiatrie und Psychotherapie sowie Gründer '
            .'und Leiter des Zentrums für Psychotraumatologie und Traumatherapie Niedersachsen, begrüßen. In seinem '
            .'Impulsvortrag erklärte er verständlich, warum traumatische Erfahrungen Bindung und Beziehung verändern '
            .'können und weshalb Reaktionen, die von außen manchmal schwer nachvollziehbar wirken, aus '
            .'traumatherapeutischer Sicht sehr verständlich sein können.',
        'Im Anschluss stand der gemeinsame Austausch im Mittelpunkt. Fragen aus dem Publikum und bereits im Vorfeld '
            .'eingereichte Fragen konnten aufgegriffen und aus unterschiedlichen Blickwinkeln betrachtet werden.',
        'Besonders wichtig war uns auch hier ein Rahmen, der möglichst wenig Druck erzeugt. Die Teilnahme war '
            .'kostenfrei, auf Wunsch anonym, Kamera und Mikrofon mussten nicht eingeschaltet werden und Fragen '
            .'konnten auch ausschließlich über den Chat gestellt werden.',
        'Für uns war dieser Abend ein gelungener Auftakt für ein Format, das genau das verbinden soll, was oft '
            .'getrennt voneinander betrachtet wird: Fachwissen trifft auf Erfahrung. Mit den Fragen, die Menschen, '
            .'die mit den Folgen von Trauma leben, beschäftigen.',
        'Denn manchmal verändert schon das Verstehen den Blick auf sich selbst und aufeinander.',
        '*KE!N EINZELFALL im Dialog – Wissen trifft Erfahrung.*',
    ];

    public function run(): void
    {
        self::anlegen();
    }

    public static function anlegen(): void
    {
        $seite = Page::query()
            ->where('slug', self::SLUG)
            ->where('locale', 'de')
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->first();

        if (! $seite) {
            return;
        }

        $vorhanden = $seite->blocks->contains(fn ($b) => ($b->data['titel'] ?? null) === self::TRAUMA_TITEL);

        if ($vorhanden) {
            return;
        }

        $warLeer = $seite->blocks->isEmpty();

        $seite->blocks()->create([
            'typ' => 'text_media',
            'position' => (int) $seite->blocks->max('position') + ($warLeer ? 0 : 1),
            'data' => [
                'titel' => self::TRAUMA_TITEL,
                'absaetze' => self::TRAUMA_ABSAETZE,
                // Stimmungsbild, sagt nichts aus: ohne Bildbeschreibung.
                'bild' => self::TRAUMA_BILD,
                'bild_seite' => 'rechts',
            ],
        ]);

        if ($warLeer) {
            $seite->update([
                'noindex' => false,
                'meta_description' => $seite->meta_description ?: self::META_DESCRIPTION,
            ]);
        }
    }
}
