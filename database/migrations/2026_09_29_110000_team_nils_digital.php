<?php

use App\Models\TeamMember;
use Database\Seeders\TeamUndGruppenSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Teamseite: Nils-Digital kommt ins Team, als „IT und Webdesign“ (KEV-67).
 *
 * Hinten ans Team. Ist der Eintrag schon da (etwa im Panel angelegt), bleibt
 * er, wie er ist.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (TeamUndGruppenSeeder::NEU_IM_TEAM as $person) {
            if (TeamMember::where('name', $person['name'])->exists()) {
                continue;
            }

            TeamMember::create($person + [
                'bereich' => TeamUndGruppenSeeder::BEREICH_TEAM,
                'position' => (int) TeamMember::max('position') + 1,
                'published_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        foreach (TeamUndGruppenSeeder::NEU_IM_TEAM as $person) {
            TeamMember::where('name', $person['name'])->delete();
        }
    }
};
