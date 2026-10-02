<?php

use App\Models\TeamMember;
use Database\Seeders\TeamUndGruppenSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Teamseite: „Herr und Frau Unbekannt“ bekommt die Zeile über dem Namen
 * zurück, „Gemeinsam KE!N EINZELFALL“ wie auf der Altseite (KEV-85).
 *
 * Nur wo die Zeile noch leer ist. Steht im Panel schon etwas, bleibt es.
 */
return new class extends Migration
{
    public function up(): void
    {
        TeamMember::where('name', 'Herr und Frau Unbekannt')
            ->where(fn ($q) => $q->whereNull('rolle')->orWhere('rolle', ''))
            ->update(['rolle' => TeamUndGruppenSeeder::UNBEKANNT_UEBERSCHRIFT]);
    }

    public function down(): void
    {
        TeamMember::where('name', 'Herr und Frau Unbekannt')
            ->where('rolle', TeamUndGruppenSeeder::UNBEKANNT_UEBERSCHRIFT)
            ->update(['rolle' => null]);
    }
};
