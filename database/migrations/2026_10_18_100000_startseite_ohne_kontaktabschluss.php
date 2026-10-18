<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Startseite: Der Abschnitt „Anfragen & Austausch“ am Ende fällt weg, Wunsch
 * des Vereins (KEV-121). Der Weg zur Anfrage bleibt über den Knopf im
 * Aufmacher und die Karte unter „Was wir gemeinsam bewegen“.
 *
 * Alle Sprachen: Die Übersetzung ist eine Kopie der deutschen Blöcke, und eine
 * schon geprüfte englische Fassung baut der Übersetzungs-Seeder nicht mehr
 * neu. Gelöscht wird jeder Kontaktabschluss der Startseite, ohne Blick auf
 * den Text: Es gab dort immer nur diesen einen, und der Wunsch meint den
 * Abschnitt, nicht einen bestimmten Wortlaut.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'contact_close')->get() as $block) {
                $block->delete();
            }
        }
    }

    public function down(): void
    {
        // Nicht zurückholen: Der Verein wollte den Abschnitt weg haben.
    }
};
