<?php

namespace Database\Seeders;

use App\Models\GlossaryTerm;
use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * Ein Glossar als eigene Seite (seit KEV-155): Seite mit Einleitung und dem
 * Baustein „Glossar“, dazu die Einträge aus einer JSON-Datei, dieser Seite
 * zugeordnet. Jedes von Taddis Glossar-Dokumenten ist ein Seeder, der hiervon
 * erbt und nur seine Konstanten setzt.
 *
 * Legt nur an, was fehlt: Seite, Bausteine, Warnung und Einträge, die der
 * Verein schon geändert hat, bleiben, wie sie sind.
 */
abstract class GlossarSeite extends Seeder
{
    public const SLUG = '';

    public const TITEL = '';

    /** @var list<string> */
    public const ABSAETZE = [];

    public const META_DESCRIPTION = '';

    /** Datei unter database/seeders/data/. */
    public const DATEI = '';

    /** @var list<string> eigene Triggerwarnung vor der Seite, leer = keine */
    public const TRIGGERWARNUNG = [];

    /**
     * KEV-262: Woraus die typischen Sätze dieser Seite stammen, für Taddis
     * Einleitung („Hier findest Du typische Formulierungen aus …“). Leer =
     * keine Sätze. Die Sätze selbst stehen unter dem SLUG in
     * data/typische-saetze.json.
     */
    public const SAETZE_AUS = '';

    public function run(): void
    {
        static::anlegen();
    }

    /** @return list<array{slug: string, kuerzel?: string, begriff: string, erklaerung?: string}> */
    public static function eintraege(): array
    {
        return json_decode(file_get_contents(database_path('seeders/data/'.static::DATEI)), true);
    }

    public static function anlegen(): void
    {
        $seite = Page::firstOrCreate(
            ['slug' => static::SLUG, 'locale' => 'de'],
            [
                'titel' => static::TITEL,
                'meta_title' => static::TITEL.' - Kein Einzelfall e.V.',
                'meta_description' => static::META_DESCRIPTION,
                'published_at' => now(),
            ],
        );

        if ($seite->wasRecentlyCreated) {
            $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['absaetze' => static::ABSAETZE]]);
            $seite->blocks()->create(['typ' => 'glossar', 'position' => 1, 'data' => []]);
            static::saetzeAnlegen($seite);

            if (static::TRIGGERWARNUNG !== []) {
                $seite->update(['triggerwarnung' => static::TRIGGERWARNUNG]);
            }
        }

        foreach (static::eintraege() as $eintrag) {
            GlossaryTerm::firstOrCreate(
                ['locale' => 'de', 'slug' => $eintrag['slug']],
                [
                    'kuerzel' => $eintrag['kuerzel'] ?? null,
                    'begriff' => $eintrag['begriff'],
                    'erklaerung' => $eintrag['erklaerung'] ?? null,
                    'seite_gruppe' => $seite->uebersetzungs_gruppe,
                    'published_at' => now(),
                ],
            );
        }

        Titelbilder::setzen();
        Titelbilder::platzhalterSetzen();
    }

    /**
     * KEV-262: Taddis „Typische Sätze – einfach übersetzt“ als Baustein unter
     * dem Glossar. Nur, wenn die Seite noch keinen hat: Was der Verein daran
     * geändert oder gelöscht hat, bleibt so.
     */
    public static function saetzeAnlegen(?Page $seite = null): void
    {
        $seite ??= Page::where('slug', static::SLUG)->where('locale', 'de')->first();
        $saetze = static::saetze();

        if (! $seite || $saetze === [] || $seite->blocks()->where('typ', 'typische_saetze')->exists()) {
            return;
        }

        $seite->blocks()->create([
            'typ' => 'typische_saetze',
            'position' => (int) $seite->blocks()->max('position') + 1,
            'data' => [
                'titel' => 'Typische Sätze – einfach übersetzt',
                'absaetze' => static::saetzeEinleitung(),
                // fest, damit /behoerden-und-bescheide#typische-saetze in
                // jeder Sprache trifft. Reihenfolge wie im Panel-Formular,
                // sonst sähe das erste Speichern wie eine Änderung aus.
                'anker' => 'typische-saetze',
                'formulierungen' => $saetze,
            ],
        ]);
    }

    /** @return list<array{satz: string, bedeutung: string}> */
    public static function saetze(): array
    {
        if (static::SAETZE_AUS === '') {
            return [];
        }

        $alle = json_decode(file_get_contents(database_path('seeders/data/typische-saetze.json')), true);

        return $alle[static::SLUG] ?? [];
    }

    /**
     * Taddis Einleitung, für alle Kategorien gleich bis auf die Herkunft der
     * Sätze. „Rechtsberatung da“ → „dar“.
     *
     * @return list<string>
     */
    public static function saetzeEinleitung(): array
    {
        return [
            'Manchmal ist nicht ein einzelnes Fachwort das Problem, sondern ein ganzer Satz.',
            'Hier findest Du typische Formulierungen aus *'.static::SAETZE_AUS.'* – übersetzt in '
                .'verständlichere Sprache.',
            'Die einfache Übersetzung soll Dir helfen, einen Satz besser einzuordnen. *Was er in Deinem '
                .'konkreten Fall bedeutet, hängt immer vom Zusammenhang ab. Dies stellt keine Rechtsberatung dar.*',
            '*Fehlt Dir hier eine Formulierung?* Dann schick uns gern eine Mail an *kontakt@kein-einzelfall.de*. '
                .'Wir ergänzen die Liste und lassen sie gemeinsam mit euch weiter wachsen.',
            '*Gemeinsam KE!N EINZELFALL*',
        ];
    }
}
