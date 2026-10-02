<?php

use App\Support\Titelbilder;
use Illuminate\Database\Migrations\Migration;

/**
 * Beschwerdemanagement: Titelbild von Taddi (KEV-80), Briefschlitz mit
 * Umschlag. setzen() füllt nur leere Felder, gepflegte bleiben stehen.
 *
 * Die Seite bleibt Entwurf, bis der Verein ihren Text schickt.
 */
return new class extends Migration
{
    public function up(): void
    {
        Titelbilder::setzen();
    }

    public function down(): void
    {
        // Bild und Unterzeile bleiben: Sie könnten inzwischen im Panel
        // gepflegt sein, und ein leerer Kopf hülfe niemandem.
    }
};
