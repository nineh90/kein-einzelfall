<?php

use App\Models\Group;
use Database\Seeders\TeamUndGruppenSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Selbsthilfegruppen neu, Texte von Taddi (KEV-73).
 *
 *  - Die fünf Gruppen mit neuem Text, Wann/Wo und Schlusssatz. Offen sind
 *    „Wir sind nicht mehr stumm“ (neu jeden 2. Mittwoch) und das
 *    Bürokratie-Labyrinth, die übrigen sind vorerst in Planung.
 *  - „Killing me Softly“ heisst jetzt „Skillin me Softly“, der Slug folgt.
 *  - Die Seite /selbsthilfegruppen: neue Einleitung, zwei Fliesstext-Blöcke
 *    der Altseite fallen weg, Dokumente ans Ende. Nur, wo noch die
 *    Einleitung der Altseite steht.
 *
 * Die Gruppentexte werden überschrieben, wie bei den Arbeitsgruppen: Im
 * Panel hat der Verein daran noch nichts geändert.
 */
return new class extends Migration
{
    public function up(): void
    {
        Group::where('slug', 'killing-me-softly')->update(['slug' => 'skillin-me-softly']);

        $neu = collect(TeamUndGruppenSeeder::gruppenliste())->where('typ', 'selbsthilfe');

        foreach ($neu->values() as $i => $gruppe) {
            Group::updateOrCreate(['slug' => $gruppe['slug']], $gruppe + [
                'position' => $i,
                'published_at' => now(),
            ]);
        }

        TeamUndGruppenSeeder::selbsthilfeseiteAufbauen();
    }

    public function down(): void
    {
        // Nichts zurück: Die alten Texte waren durch diese ersetzt.
    }
};
