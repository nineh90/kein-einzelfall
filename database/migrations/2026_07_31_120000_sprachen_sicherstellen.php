<?php

use Database\Seeders\SprachenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Legt die Sprachen des Startbestands an, falls sie fehlen (bis zum
 * 19.09.2026 Deutsch, Englisch, Russisch; seitdem ohne Russisch — siehe
 * SprachenSeeder und die Migration `russisch_entfernen`).
 *
 * Dieselbe Ursache wie bei der Startseite: Der `SprachenSeeder` läuft nur bei
 * leerer Datenbank (über den `AltseiteSeeder`), auf dem Server gar nicht. Wer
 * die Seiten schon hatte, bekam die Sprachtabelle nie — im Panel stand unter
 * „Sprachen“ nichts, und der Sprachumschalter hatte nichts anzubieten.
 *
 * Bewusst nur die Sprach-*Zeilen*, kein Inhalt und keine Freischaltung:
 * Englisch bleibt `aktiv = false`. Das ist die Sicherheitslinie
 * aus dem Projekt — eine Sprache wird erst sichtbar, wenn der Verein ihre
 * Inhalte freigegeben hat. Die maschinellen Demo-Übersetzungen schaltet ein
 * eigener Seeder frei, der bewusst *nicht* automatisch auf dem Server läuft.
 */
return new class extends Migration
{
    public function up(): void
    {
        // updateOrCreate im Seeder macht den Aufruf idempotent.
        Artisan::call('db:seed', [
            '--class' => SprachenSeeder::class,
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        // Bewusst nichts: Seiten, Weiterleitungen und Übersetzungen hängen an
        // den Sprachzeilen. Sie zu löschen risse mehr ein, als es aufräumt.
    }
};
