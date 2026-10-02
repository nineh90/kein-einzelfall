<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Migrations\Migration;

/**
 * Istanbul-Konvention und Kinderkodex: Titelbild von Taddi statt des
 * Platzhalters (KEV-79), ein Ordner mit dem Vereinslogo. Ersetzt wird nur
 * der Platzhalter; ein im Panel gepflegtes Bild bleibt.
 */
return new class extends Migration
{
    private const SEITEN = ['istanbul-konvention', 'kinderkodex'];

    public function up(): void
    {
        Page::whereIn('slug', self::SEITEN)
            ->where('titelbild', Titelbilder::PLATZHALTER)
            ->update(['titelbild' => null]);

        Titelbilder::setzen();
    }

    public function down(): void
    {
        Page::whereIn('slug', self::SEITEN)
            ->where('titelbild', Titelbilder::datei('kinderkodex'))
            ->update(['titelbild' => Titelbilder::PLATZHALTER]);
    }
};
