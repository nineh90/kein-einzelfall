<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Migrations\Migration;

/**
 * Schutz- und Wertekonzept und Red Flags: Titelbilder von Taddi statt des
 * Platzhalters (aus ihrem Drive-Ordner „1. Verein“, ohne eigenes Ticket;
 * die Seiten stammen aus KEV-97). Ersetzt wird nur der Platzhalter; ein im
 * Panel gepflegtes Bild bleibt.
 */
return new class extends Migration
{
    private const SLUGS = ['schutz-und-wertekonzept', 'red-flags'];

    public function up(): void
    {
        foreach (self::SLUGS as $slug) {
            $gruppe = Page::where('slug', $slug)->where('locale', 'de')->value('uebersetzungs_gruppe');

            Page::where('uebersetzungs_gruppe', $gruppe)
                ->where('titelbild', Titelbilder::PLATZHALTER)
                ->update(['titelbild' => null]);
        }

        Titelbilder::setzen();
    }

    public function down(): void
    {
        foreach (self::SLUGS as $slug) {
            $gruppe = Page::where('slug', $slug)->where('locale', 'de')->value('uebersetzungs_gruppe');

            Page::where('uebersetzungs_gruppe', $gruppe)
                ->where('titelbild', Titelbilder::datei($slug))
                ->update(['titelbild' => Titelbilder::PLATZHALTER]);
        }
    }
};
