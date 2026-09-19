<?php

use App\Models\Page;
use Database\Seeders\StartseiteSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Trägt die Spendenmöglichkeit auf der Startseite nach (KEV-10).
 *
 * Auf der Startseite gab es drei Links auf /spenden — Menü, Einstiegskarte,
 * Hinweisband — aber nirgends die Möglichkeit selbst. In der Besprechung vom
 * 02.08.2026 wurde beschlossen, sie direkt dort zu verankern: Konto mit
 * QR-Code und PayPal, ohne dass jemand erst eine Unterseite suchen muss.
 *
 * Alle Sprachfassungen der Startseite bekommen den Baustein, nicht nur die
 * deutsche: Die englische und russische Fassung sind Klone mit denselben
 * Bausteinen, und eine Startseite ohne den Abschnitt fiele dort auf. Konto und
 * PayPal sind sprachneutral; die Beschriftungen des Bausteins kommen aus den
 * Sprachdateien. Nur Einleitung und Verweis stehen dort erst einmal deutsch —
 * wie jeder andere Vereinstext, den der Verein im Panel übersetzt.
 *
 * Warum eine Migration und nicht der Seeder: Der legt die Startseite nur auf
 * leerer Datenbank an — auf dem Server und auf jedem eingerichteten Rechner
 * gibt es sie schon. Hier wird nur eingefügt, was fehlt.
 */
return new class extends Migration
{
    public function up(): void
    {
        // foreach, nicht ->each(): Der Rückgabewert false („steht schon da“)
        // würde each() abbrechen — hat die deutsche Startseite den Baustein
        // schon, bekäme ihn die englische nie.
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->get() as $seite) {
            StartseiteSeeder::spendenAnhaengen($seite);
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
