<?php

use App\Models\Page;
use Database\Seeders\BarrierefreiheitSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Hängt die Übersicht „Gespeicherte Einstellungen“ an die Seite
 * /barrierefreiheit — den Ort, an dem sich zurücksetzen lässt, was diese
 * Website im Browser abgelegt hat.
 *
 * Warum das nötig wurde: Bis zur Trigger-Warnung gab es genau einen
 * gespeicherten Wert, und die Darstellungs-Toolbar hatte ihren eigenen Knopf
 * dafür. Mit dem zweiten Wert gab es keinen Weg mehr zurück — wer „Hinweis
 * nicht mehr anzeigen“ gewählt hatte, konnte das nirgends widerrufen. Auf einem
 * geteilten Gerät ist das kein theoretisches Problem.
 *
 * Warum eine Migration und nicht der Seeder: Der löscht die Bausteine der Seite
 * und legt sie neu an — auf einer Installation, auf der der Verein den Text
 * angepasst hat, wäre das ein Datenverlust. Hier wird nur angehängt, was fehlt.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seite = Page::where('slug', 'barrierefreiheit')->first();

        // Gibt es die Seite nicht, ist die Datenbank noch leer — dann legt sie
        // der Seeder ohnehin gleich samt Übersicht an.
        if ($seite) {
            BarrierefreiheitSeeder::speicherUebersichtAnhaengen($seite);
        }
    }

    public function down(): void
    {
        /*
         * Bewusst nichts — wie bei den übrigen Inhalts-Migrationen. Einen
         * Baustein zu entfernen, an dem zwischenzeitlich jemand gearbeitet hat,
         * ist der schlechtere Zustand von beiden.
         */
    }
};
