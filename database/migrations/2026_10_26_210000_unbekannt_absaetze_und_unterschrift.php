<?php

use App\Models\TeamMember;
use Database\Seeders\TeamUndGruppenSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * „Herr und Frau Unbekannt“: Taddis Text mit Absätzen und der Unterschrift
 * „– DANKE! Herr und Frau Unbekannt“ (KEV-188). Nur solange der Text noch
 * der eine Absatz der Altseite ist. Die englische Fassung aus
 * uebersetzungen-team.json gleich mit.
 */
return new class extends Migration
{
    public function up(): void
    {
        $person = TeamMember::where('name', 'Herr und Frau Unbekannt')->first();

        if (! $person || substr_count((string) $person->getRawOriginal('profil'), '<p>') !== 1) {
            return;
        }

        $absaetze = TeamUndGruppenSeeder::UNBEKANNT_ABSAETZE;

        $person->kurzprofil = TeamUndGruppenSeeder::kurzfassung($absaetze[0]);
        $person->profil = collect($absaetze)->map(fn ($a) => '<p>'.e($a).'</p>')->implode("\n");
        $person->signatur = TeamUndGruppenSeeder::UNBEKANNT_SIGNATUR;

        $en = json_decode(file_get_contents(database_path('seeders/data/uebersetzungen-team.json')), true)['Herr und Frau Unbekannt'] ?? null;
        if ($en) {
            $uebersetzungen = $person->uebersetzungen ?? [];
            $uebersetzungen['en'] = array_replace($uebersetzungen['en'] ?? [], array_intersect_key($en, array_flip(['kurzprofil', 'profil', 'signatur'])));
            $person->uebersetzungen = $uebersetzungen;
        }

        $person->save();
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
