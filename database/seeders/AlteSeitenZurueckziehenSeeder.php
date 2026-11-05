<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Redirect;
use Illuminate\Database\Seeder;

/**
 * Seiten der alten Website offline nehmen (KEV-246).
 *
 * Taddi, 04.11.2026, zu den alten Seiten unter „Wissen“: „die sollen garnicht
 * auf die seite […] die sollen erstmal nicht mit drauf. wenn ich viel zeit
 * habe, schau ich mir das an.“ Im Menü standen sie seit KEV-239 schon nicht
 * mehr, erreichbar waren sie aber noch.
 *
 * Nicht gelöscht, nur zurückgezogen (Entwurf): Taddi will sie sich später
 * ansehen, im Panel stehen sie weiter. Ihre Adressen waren auf der alten
 * Website indexiert und verlinkt, deshalb leiten sie auf die Seite weiter,
 * die heute das Thema trägt. Vorübergehend (302), weil sie zurückkommen
 * können. Wird eine Seite wieder veröffentlicht, gilt die Seite, nicht die
 * Weiterleitung (PageController).
 *
 * Die vier Abende von „KE!N EINZELFALL im Dialog“ und die alte Übersicht der
 * Reihe waren Ankündigungen; die Reihe steht seit KEV-317 mit ihren Terminen
 * auf /veranstaltungen. Die Erwerbsminderungsrente hat unter „Vorlagen &
 * Schreiben“ eine eigene Kategorie (KEV-323).
 *
 * Bewusst nicht dabei: „FSM – Erweitertes Hilfesystem“. Dort stehen die
 * Stellungnahmen des Vereins und der Schriftwechsel mit dem Ministerium,
 * keine allgemeinen Infos, und der Rückblick verlinkt darauf. Rückfrage an
 * Taddi in der Übergabe-Checkliste (A17).
 *
 * Aufgerufen von der Migration (bestehende Datenbanken) und vom
 * AltseiteSeeder (frische), der die Seiten erst anlegt. Läuft beliebig oft.
 */
class AlteSeitenZurueckziehenSeeder extends Seeder
{
    /** Slug => Ziel der Weiterleitung */
    public const SEITEN = [
        'trauma-bindung-und-beziehung' => '/veranstaltungen#reihe-dialog-wissen',
        'das-hilfesystem' => '/veranstaltungen#reihe-dialog-wissen',
        'traumafolgestoerungen-verstehen' => '/veranstaltungen#reihe-dialog-wissen',
        'buerokratie-labyrinth' => '/veranstaltungen#reihe-dialog-wissen',
        'kein-einzelfall-im-dialog' => '/veranstaltungen#reihe-dialog-wissen',
        'erwerbsminderungsrente' => '/vorlagen-reha-und-erwerbsminderung',
    ];

    public function run(): void
    {
        $anzahl = self::zurueckziehen();

        $this->command?->info("{$anzahl} alte Seiten zurückgezogen.");
    }

    /** @return int wie viele Seiten (alle Sprachen und Fassungen) offline gingen */
    public static function zurueckziehen(): int
    {
        // Alle Sprachen und Fassungen: Der UebersetzungenSeeder übersetzt nur
        // veröffentlichte deutsche Seiten, räumt eine schon angelegte
        // englische aber nicht von selbst weg.
        $anzahl = Page::query()
            ->whereIn('slug', array_keys(self::SEITEN))
            ->whereNotNull('published_at')
            ->update(['published_at' => null]);

        foreach (self::SEITEN as $slug => $ziel) {
            // Mit und ohne Schrägstrich: Die Regel „/x/ → /x“ der Altseite
            // führte sonst auf eine Seite, die es nicht mehr gibt.
            foreach ([$slug, $slug.'/'] as $von) {
                Redirect::updateOrCreate(
                    ['von' => $von],
                    ['nach' => $ziel, 'status' => 302, 'notiz' => 'Alte Seite zurückgezogen (KEV-246)'],
                );
            }
        }

        return $anzahl;
    }
}
