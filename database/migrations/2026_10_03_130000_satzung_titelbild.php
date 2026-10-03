<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Migrations\Migration;

/**
 * Satzung: Titelbild von Taddi statt des Platzhalters (KEV-77), ein
 * aufgeschlagenes Buch mit Füller. Ersetzt wird nur der Platzhalter; ein im
 * Panel gepflegtes Bild bleibt.
 */
return new class extends Migration
{
    public function up(): void
    {
        $gruppe = Page::where('slug', 'satzung')->where('locale', 'de')->value('uebersetzungs_gruppe');

        Page::where('uebersetzungs_gruppe', $gruppe)
            ->where('titelbild', Titelbilder::PLATZHALTER)
            ->update(['titelbild' => null]);

        Titelbilder::setzen();
    }

    public function down(): void
    {
        $gruppe = Page::where('slug', 'satzung')->where('locale', 'de')->value('uebersetzungs_gruppe');

        Page::where('uebersetzungs_gruppe', $gruppe)
            ->where('titelbild', Titelbilder::datei('satzung'))
            ->update(['titelbild' => Titelbilder::PLATZHALTER]);
    }
};
