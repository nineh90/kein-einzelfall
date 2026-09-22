<?php

use Database\Seeders\TeamUndGruppenSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Baut die Teamseite nach — was team_vervollstaendigen versäumt hat.
 *
 * Die Vorgänger-Migration schützte den Bestand mit einem Wächter:
 *
 *     $raster = $seite?->blocks()->where('typ', 'team_grid')->get();
 *     $unveraendert = $raster && $raster->count() === 1 && empty($raster->first()->data);
 *
 * Verlangt war also genau ein team_grid-Block ohne Daten. Diesen Zustand gibt
 * es nirgends dauerhaft: TeamUndGruppenSeeder::seitenNeuAufbauen() legt ihn in
 * der einen Zeile an und lässt ihn in der nächsten von teamseiteAufbauen()
 * wieder löschen — das setzt die Seite mit einem team_grid je Bereich neu
 * zusammen, alle mit Daten. Auf einer frisch eingerichteten Datenbank stehen
 * am Ende drei gefüllte Raster, auf dem Server aus dem Alt-Import gar keines.
 * Drei ist nicht eins, null auch nicht — die Bedingung war in beide Richtungen
 * unerfüllbar, der Rumpf ist nie gelaufen.
 *
 * Aufgefallen ist es erst auf der Demo: `migrate` meldete die Migration als
 * erledigt, `team()` hatte die Porträts auch sauber an die Personen gehängt —
 * nur die Seite zeigte weiter den kaputten Abzug vom Juli, in dem Tatjana
 * Belmar über Franziska Künstlers Abimotto und Petra Hildebrandts Kassenbuch
 * spricht. Ein stiller Fehlschlag: im Log von einem Erfolg nicht zu
 * unterscheiden.
 *
 * Warum eine zweite Migration und kein Nachbessern der ersten: Die erste steht
 * überall als `Ran` in der Datenbank und wird nie wieder angefasst. Nur ein
 * neuer Eintrag erreicht eine bereits gefüllte Installation.
 *
 * Der Wächter entfällt hier ersatzlos. Was er schützen sollte — im Panel
 * geänderte Seiten —, gibt es nicht: Die Seite ist noch nicht übergeben, auf
 * der Demo hat niemand daran gearbeitet (nachgesehen, nicht vermutet). Ihn
 * zu reparieren hiesse, eine Bedingung zu formulieren, die „von Hand
 * bearbeitet" von „falsch importiert" unterscheidet — beides sind Textblöcke,
 * und der Unterschied steht nirgends in der Datenbank.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new TeamUndGruppenSeeder)->teamseiteAufbauen();
    }

    public function down(): void
    {
        /*
         * Bewusst nichts. Zurück führte nur in die vermischten Biografien des
         * Juli-Abzugs — dorthin will niemand, und der Weg dahin stünde ohnehin
         * nur offen, solange seither nichts geändert wurde.
         */
    }
};
