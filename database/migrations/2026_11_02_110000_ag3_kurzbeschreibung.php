<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;

/**
 * ag-03-online-veranstaltungen: neue Kurzbeschreibung „Aus Ideen werden Begegnungen.“ (KEV-315), steht auf der Karte
 * und unter der Überschrift der Seite. Nur wo noch die alte steht, auf
 * Englisch ebenso.
 */
return new class extends Migration
{
    private const ALT = 'Aus einer Idee wird ein Termin – und aus einem Termin ein Raum für Austausch, Wissen und Begegnung.';

    private const NEU = 'Aus Ideen werden Begegnungen.';

    private const ALT_EN = 'An idea becomes a date – and a date becomes a space for exchange, knowledge and meeting others.';

    private const NEU_EN = 'Ideas turn into encounters.';

    public function up(): void
    {
        $gruppe = Group::where('slug', 'ag-03-online-veranstaltungen')->first();

        if (! $gruppe) {
            return;
        }

        if ($gruppe->teaser === self::ALT) {
            $gruppe->teaser = self::NEU;
        }

        $u = $gruppe->uebersetzungen ?? [];
        if (($u['en']['teaser'] ?? null) === self::ALT_EN) {
            $u['en']['teaser'] = self::NEU_EN;
            $gruppe->uebersetzungen = $u;
        }

        $gruppe->save();
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
