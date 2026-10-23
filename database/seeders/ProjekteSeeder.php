<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Bereich „Projekte“ (KEV-160): Übersichtsseite, die Projekte selbst folgen
 * später als Unterseiten.
 *
 * Die Seite gibt es seit dem NeueBereicheSeeder als Entwurf mit zwei leeren
 * Überschriften aus dem Strukturpapier („Laufende Projekte“, „Abgeschlossene
 * Projekte“). Jetzt mit Taddis Text, veröffentlicht und im Menü als eigener
 * Bereich nach „Raum für Ungesagtes“ (ihr Drive-Ordner „5. Projekte“ nach
 * „4. Raum für Ungesagtes“). Ihr Bild (Hand heftet Zettel an eine Wand)
 * ersetzt unser Holzhaus-Modell, gleicher Dateiname.
 *
 * Aufbau wie die Übersicht „Raum für Ungesagtes“: Text, Kacheln der
 * Unterseiten, Aufruf. Die Kacheln zeigen sich erst, wenn im Menü Projekte
 * unter dem Bereich stehen (PageBlock::zeigtNichts). Der Aufruf steht
 * linksbündig: Ohne Kacheln folgt er direkt auf den Text, und zentriert in
 * dessen Spalte wirkte er verrutscht.
 *
 * Taddis Text kam im Ticket in einer Zeile, Absätze von uns. „Du hast eine
 * eigene Projektidee?“ ist die Überschrift des Aufrufs, „Das Team von KE!N
 * EINZELFALL“ der handschriftliche Gruß wie auf „Gruppen & Veranstaltungen“,
 * „Für Sichtbarkeit, Gehör und Unterstützung.“ als Leitsatz fett.
 * Korrigiert: Zitate mit „…“, Doppelpunkt und Großschreibung vor dem ersten
 * Zitat, kein Komma vor „oder auf die Beine stellen“. Die Adresse
 * „arbeitsgruppen@“ heißt wie überall auf der Website arbeitsgruppe@
 * (Group::AG_ADRESSE), Abgleich in der Übergabe-Checkliste.
 *
 * Legt nur an, was fehlt: Die leeren Überschriften werden ersetzt, ein
 * Text, den der Verein schon eingetragen hat, bleibt.
 */
class ProjekteSeeder extends Seeder
{
    public const SLUG = 'projekte';

    public const TITEL = 'Projekte';

    public const ABSAETZE = [
        'Aus Erfahrungen und Bedarf entstehen Ideen, gemeinsam entwickeln wir daraus Projekte, die etwas bewegen '
            .'können.',
        'Unsere Projekte sind so vielfältig wie die Themen, Menschen und Perspektiven, die bei KE!N EINZELFALL '
            .'zusammenkommen. Sie können kreativ, informativ, fachlich, öffentlich oder gesellschaftlich ausgerichtet '
            .'sein – von Ausstellungen und Beteiligungsformaten bis hin zu Vorträgen, Fachveranstaltungen oder '
            .'gemeinsamen Veröffentlichungen.',
        'Manche Projekte machen Erfahrungen sichtbar, andere vermitteln Wissen, schaffen Austausch oder bringen '
            .'Betroffenenperspektiven in fachliche und gesellschaftliche Räume.',
        'Für uns sind Projekte ein wichtiger Raum für Selbstwirksamkeit. Hier können Menschen eigene Erfahrungen, '
            .'Wissen, Ideen und Fähigkeiten einbringen und gemeinsam mit anderen etwas entstehen lassen.',
        'Solange ein Projekt noch nicht abgeschlossen ist, kannst du jederzeit einsteigen – egal, ob du Wissen '
            .'mitbringst, kreativ sein möchtest, eigene Erfahrungen einbringen kannst oder einfach den Wunsch hast, '
            .'dabei zu sein und mitzugestalten.',
    ];

    /** Überschrift über den Kacheln. Von uns. */
    public const KACHELN_TITEL = 'Unsere Projekte';

    public const AUFRUF_TITEL = 'Du hast eine eigene Projektidee?';

    public const AUFRUF = [
        'Und vielleicht denkst du grad: „Auf dieser Seite fehlt noch ein ganz bestimmtes Projekt.“ Oder: „Boah, das '
            .'wollte ich schon immer mit anderen teilen oder auf die Beine stellen.“',
        'Dann schreib uns an '.Group::AG_ADRESSE.'.',
        'Du brauchst kein fertiges Konzept. Engagement und eine Idee sind genug.',
        'Gemeinsam schauen wir, wie sich deine Idee umsetzen lässt und welche Unterstützung durch KE!N EINZELFALL '
            .'möglich ist.',
        '*Für Sichtbarkeit, Gehör und Unterstützung.*',
    ];

    public const GRUSS = 'Das Team von KE!N EINZELFALL';

    public const META_DESCRIPTION = 'Projekte von KE!N EINZELFALL e.V.: Ausstellungen, Beteiligungsformate, '
        .'Vorträge und Veröffentlichungen. Steig ein oder bring deine eigene Projektidee mit.';

    /** Die Überschriften aus dem Strukturpapier, ohne Text (NeueBereicheSeeder). */
    private const LEERE_UEBERSCHRIFTEN = ['Laufende Projekte', 'Abgeschlossene Projekte'];

    public function run(): void
    {
        self::anlegen();
    }

    public static function anlegen(): void
    {
        $seite = Page::firstOrCreate(
            ['slug' => self::SLUG, 'locale' => 'de'],
            ['titel' => self::TITEL, 'meta_title' => self::TITEL.' - Kein Einzelfall e.V.'],
        );

        // Nur leere Überschriften, nichts, was der Verein geschrieben hat.
        $seite->blocks()->where('typ', 'text')->get()
            ->filter(fn ($b) => in_array($b->data['titel'] ?? null, self::LEERE_UEBERSCHRIFTEN, true)
                && blank($b->data['absaetze'] ?? null))
            ->each->delete();

        if ($seite->blocks()->doesntExist()) {
            $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['absaetze' => self::ABSAETZE]]);
            $seite->blocks()->create(['typ' => 'unterseiten', 'position' => 1, 'data' => ['titel' => self::KACHELN_TITEL]]);
            $seite->blocks()->create(['typ' => 'text', 'position' => 2, 'data' => [
                'titel' => self::AUFRUF_TITEL,
                'absaetze' => self::AUFRUF,
                'hand' => self::GRUSS,
            ]]);
        }

        // Bisher Entwurf: Mit Text geht die Seite online und darf gefunden werden.
        if ($seite->published_at === null) {
            $seite->update([
                'published_at' => now(),
                'noindex' => false,
                'meta_description' => $seite->meta_description ?: self::META_DESCRIPTION,
            ]);
        }
    }
}
