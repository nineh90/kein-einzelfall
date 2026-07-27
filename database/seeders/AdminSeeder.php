<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Legt ein Konto für die Verwaltung an, falls noch keines existiert.
 *
 * Vorher wurde das Konto von Hand per `make:filament-user` erzeugt — auf genau
 * einem Rechner. Auf jedem anderen fehlte es, während das README behauptete,
 * es gäbe eines. Ein Konto, das nur in einer bestimmten Datenbank existiert,
 * ist keine brauchbare Grundlage.
 *
 * Nur für die lokale Entwicklung: In Produktion legt man das erste Konto von
 * Hand an und vergibt ein eigenes Passwort.
 */
class AdminSeeder extends Seeder
{
    public const EMAIL = 'admin@kein-einzelfall.test';

    public const PASSWORT = 'kein-einzelfall';

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('AdminSeeder übersprungen — nicht für Produktion gedacht.');

            return;
        }

        // Existiert schon irgendein freigeschaltetes Konto, wird nichts angelegt:
        // sonst käme bei jedem Start ein weiteres dazu.
        if (User::where('panel_zugang', true)->exists()) {
            return;
        }

        User::updateOrCreate(['email' => self::EMAIL], [
            'name' => 'Verwaltung',
            'password' => Hash::make(self::PASSWORT),
            'panel_zugang' => true,
            'email_verified_at' => now(),
        ]);

        $this->command?->info('Verwaltungs-Konto angelegt: '.self::EMAIL.' / '.self::PASSWORT);
    }
}
