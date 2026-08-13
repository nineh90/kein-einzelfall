<?php

use App\Models\Group;
use Database\Seeders\TeamUndGruppenSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Trägt die Gruppen aus dem Strukturpapier des Vereins nach, die auf der
 * Altseite noch nicht standen: die Selbsthilfegruppe „Killing me Softly“ und
 * die AG 07 („Traumabegleiter“-App).
 *
 * Warum eine Migration: Seeder laufen bei uns nur bei leerer Datenbank und auf
 * dem Server gar nicht. Dasselbe Muster wie bei der Startseite.
 *
 * Warum nicht einfach `db:seed --class=TeamUndGruppenSeeder`: Der schreibt mit
 * `updateOrCreate` über den gesamten Bestand und machte damit jede Änderung
 * zunichte, die der Verein im Panel an einer bestehenden Gruppe vorgenommen
 * hat. Hier wird nur angelegt, was fehlt — vorhandene Gruppen bleiben
 * unangetastet.
 */
return new class extends Migration
{
    private const NACHTRAG = ['killing-me-softly', 'ag-07-traumabegleiter'];

    public function up(): void
    {
        // Ans Ende der jeweiligen Liste, statt bestehende Positionen zu
        // verschieben: Die Reihenfolge im Panel ist eine Entscheidung des
        // Vereins und keine, die eine Migration treffen sollte.
        $naechstePosition = (int) Group::max('position') + 1;

        foreach (TeamUndGruppenSeeder::gruppenliste() as $gruppe) {
            if (! in_array($gruppe['slug'], self::NACHTRAG, true)) {
                continue;
            }

            if (Group::where('slug', $gruppe['slug'])->exists()) {
                continue;
            }

            Group::create(array_merge($gruppe, [
                'position' => $naechstePosition++,
                'published_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        /*
         * Bewusst nichts. Ein Rücklauf löschte eine Gruppe, an der der Verein
         * zwischenzeitlich gearbeitet haben kann — wiederherstellen liesse sie
         * sich im Panel nicht.
         */
    }
};
