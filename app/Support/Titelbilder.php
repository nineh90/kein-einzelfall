<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Facades\Schema;

/**
 * Die Titelbilder der Inhaltsseiten (Seitenkopf, rechts neben dem Titel).
 *
 * Mit fal.ai erzeugt, nach der Bildvorgabe des Vereins (Taddi, 24.09.2026):
 * ruhige Stillleben in Sand- und Cremetönen, Licht von links oben, Motiv links
 * unten. Vorlage und Motive stehen in docs/Bildsprache.md.
 *
 * Rechtstexte, Barrierefreiheit, die Trigger-Warnung und die Themenseiten
 * unter „Wissen“ bekommen bewusst keins: Dort geht es nur um den Text, und
 * zwanzig fast gleiche Schreibtischbilder machten die Reihe beliebig.
 *
 * Gebraucht von der Migration (bestehende Datenbanken) und vom
 * AltseiteSeeder (frisch aufgebaute).
 */
class Titelbilder
{
    public const ORDNER = '/img/titelbilder';

    /**
     * Seiten mit Titelbild und ihre Unterzeile.
     *
     * Die Unterzeile steht auf dem Bild unter dem Titel, kurz, höchstens zwei
     * Zeilen. Wo es ging, ist es ein Satz des Vereins. Nicht aber die erste
     * Überschrift der Seite, die stünde sonst zweimal untereinander. Die
     * mit „von uns“ markierten sind neu formuliert und stehen zum Gegenlesen
     * in der Übergabe-Checkliste (A14).
     */
    public const SEITEN = [
        'verein' => 'Opferhilfe für soziale Gerechtigkeit!',
        'ueber-uns-vorstand-und-team' => 'Die Menschen hinter unserer Arbeit.',                    // von uns
        'mitgliedschaft' => 'Werde Teil unseres Netzwerks!',
        'spenden' => 'Sei Du dabei, jede Unterstützung zählt, egal wie gering!',
        'unterstuetzung' => 'Wichtiges über Rechte, Anträge und unsere Arbeit.',                  // von uns
        'selbsthilfegruppen' => 'Raum für deine Geschichte – ohne Druck oder Bewertung',
        'arbeitsgruppen' => 'Mach mit – mit Fachwissen, Kreativität oder einfach dem Wunsch, etwas zu bewegen.',
        'anfragen' => 'Persönlicher Austausch zu Entschädigung, Schwerbehinderung und Pflegegrad.', // von uns
        'kontakt' => 'Alle Ansprechpartner, Landesstellen und Zuständigkeiten auf einen Blick',
        'wissen' => 'Rechte, Anträge und Hilfesysteme verständlich erklärt.',                     // von uns
        'publikationen' => 'Umfragen und Veröffentlichungen des Vereins.',                         // von uns
        'veranstaltungen' => 'Wissen teilen. Erfahrungen verbinden. Gemeinsam Perspektiven schaffen.',
        'projekte' => 'Woran wir gerade arbeiten.',                                                // von uns
        'kein-einzelfall-im-dialog' => 'Wissenschaftliche Erkenntnisse und gelebte Erfahrung im Austausch.', // von uns
        'soziales-entschaedigungsrecht' => 'Hilfe für Menschen, die durch eine Gewalttat geschädigt wurden.', // von uns
        'traumafolgestoerungen-verstehen' => 'Was eine Traumafolgestörung ist – und wie man passende Hilfe findet.', // von uns
        'trauma-bindung-und-beziehung' => 'Warum ein Trauma Beziehungen verändert – und was dabei hilft.', // von uns
    ];

    /**
     * Setzt die Bilder. Die Übersetzungen einer Seite bekommen dasselbe —
     * gefunden über die Übersetzungsgruppe, nicht über den Slug, der je
     * Sprache ein anderer sein darf. Das Bild zeigt keinen Text.
     *
     * Schon gepflegte Titelbilder bleiben stehen.
     */
    public static function setzen(): void
    {
        $woerterbuch = json_decode(
            (string) @file_get_contents(base_path('database/seeders/data/uebersetzungen.json')),
            true,
        ) ?? [];

        foreach (self::SEITEN as $slug => $untertitel) {
            $gruppe = Page::query()
                ->where('slug', $slug)
                ->where('locale', 'de')
                ->where('fassung', Page::FASSUNG_STANDARD)
                ->value('uebersetzungs_gruppe');

            if (! $gruppe) {
                continue;
            }

            Page::where('uebersetzungs_gruppe', $gruppe)
                ->whereNull('titelbild')
                ->update(['titelbild' => self::ORDNER."/{$slug}.webp"]);

            // Die Spalte kam später (Migration vom 24.09.2026, 14 Uhr). Läuft
            // die ältere Titelbild-Migration auf frischer Datenbank, gibt es
            // sie noch nicht; die neue ruft setzen() danach noch einmal auf.
            if (! Schema::hasColumn('pages', 'untertitel')) {
                continue;
            }

            // Die Unterzeile ist Text und je Sprache eine andere. Fehlt die
            // Übersetzung, bleibt sie leer, statt Deutsch auf eine englische
            // Seite zu schreiben.
            Page::where('uebersetzungs_gruppe', $gruppe)
                ->whereNull('untertitel')
                ->get()
                ->each(function (Page $seite) use ($untertitel, $woerterbuch) {
                    $text = $seite->locale === 'de'
                        ? $untertitel
                        : ($woerterbuch[$untertitel][$seite->locale] ?? null);

                    if ($text) {
                        $seite->update(['untertitel' => $text]);
                    }
                });
        }
    }

    /**
     * Titelbild einer Seite, die nicht aus der Seitentabelle gezeigt wird
     * (etwa /veranstaltungen, eine eigene Übersicht). Gepflegt wird es
     * trotzdem an der gleichnamigen Seite im Panel.
     */
    public static function fuer(string $slug): ?string
    {
        return Page::query()
            ->where('slug', $slug)
            ->where('locale', 'de')
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->value('titelbild');
    }

    public static function entfernen(): void
    {
        Page::where('titelbild', 'like', self::ORDNER.'/%')->update(['titelbild' => null]);
    }
}
