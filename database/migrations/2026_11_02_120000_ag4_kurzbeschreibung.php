<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;

/**
 * ag-04-soziale-medien: neue Kurzbeschreibung „Aus Ideen werden Beiträge, die Betroffene und ihre Anliegen sichtbar machen.“ (KEV-316), steht auf der Karte
 * und unter der Überschrift der Seite. Nur wo noch die alte steht, auf
 * Englisch ebenso.
 */
return new class extends Migration
{
    private const ALT = 'Sichtbarkeit entsteht nicht von allein.';

    private const NEU = 'Aus Ideen werden Beiträge, die Betroffene und ihre Anliegen sichtbar machen.';

    private const ALT_EN = 'Visibility does not happen on its own.';

    private const NEU_EN = 'Ideas turn into posts that make people affected and their concerns visible.';

    public function up(): void
    {
        $gruppe = Group::where('slug', 'ag-04-soziale-medien')->first();

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
