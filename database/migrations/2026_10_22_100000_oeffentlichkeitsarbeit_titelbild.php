<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Migrations\Migration;

/**
 * Öffentlichkeitsarbeit: Titelbild von Taddi statt des Platzhalters
 * (KEV-149), ein Megafon auf einem Holztisch. Ersetzt wird nur der
 * Platzhalter; ein im Panel gepflegtes Bild bleibt.
 */
return new class extends Migration
{
    private const SLUG = 'oeffentlichkeitsarbeit';

    public function up(): void
    {
        $gruppe = Page::where('slug', self::SLUG)->where('locale', 'de')->value('uebersetzungs_gruppe');

        Page::where('uebersetzungs_gruppe', $gruppe)
            ->where('titelbild', Titelbilder::PLATZHALTER)
            ->update(['titelbild' => null]);

        Titelbilder::setzen();
    }

    public function down(): void
    {
        $gruppe = Page::where('slug', self::SLUG)->where('locale', 'de')->value('uebersetzungs_gruppe');

        Page::where('uebersetzungs_gruppe', $gruppe)
            ->where('titelbild', Titelbilder::datei(self::SLUG))
            ->update(['titelbild' => Titelbilder::PLATZHALTER]);
    }
};
