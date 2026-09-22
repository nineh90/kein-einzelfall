<?php

use Database\Seeders\AltseiteSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Schreibt die Seitentitel aus, die noch aus dem Slug abgeleitet waren.
 *
 * Neun Seiten der Altseite weisen keine Überschrift aus — der Importer findet
 * dort kein <h1>. Der Seeder hat für genau diesen Fall eine Liste
 * ausgeschriebener Titel (AltseiteSeeder::TITEL), die aber erst nach dem
 * ersten Import dazukam. Seeder laufen bei uns nur bei leerer Datenbank und
 * auf dem Server gar nicht, also blieb der Notbehelf stehen: Slugs kennen
 * keine Umlaute, und so stand über der Teamseite „Ueber Uns Vorstand Und
 * Team" — in der Überschrift, im Brotkrumenpfad und im Reiter des Browsers.
 *
 * Warum eine Migration: dasselbe Muster wie bei team_vervollstaendigen. Sie
 * ist der einzige Weg, auf dem eine Korrektur eine bereits gefüllte Datenbank
 * erreicht.
 *
 * Die Titel sind wortgleich mit den Menüpunkten aus config/navigation.php —
 * geprüft, nicht angenommen: Ein Menüpunkt, der anders heisst als die
 * Überschrift der Seite, auf der man landet, lässt zweifeln, ob man richtig
 * ist. Für Menschen, die sich ohnehin schwer orientieren, ist das keine
 * Kleinigkeit.
 */
return new class extends Migration
{
    public function up(): void
    {
        AltseiteSeeder::titelNachziehen();
    }

    public function down(): void
    {
        /*
         * Bewusst nichts. Zurück ginge es nur in einen Titel mit fehlenden
         * Umlauten — das will niemand, und rückgängig machen liesse sich
         * ohnehin nur, was seither unverändert blieb.
         */
    }
};
