<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Legt lokal ein Konto für den Vereinsbereich (/intern) an, damit man sieht,
 * was der Verein sieht. Wie der AdminSeeder nur für die Entwicklung: In
 * Produktion legt `php artisan verein:konto` das Konto an.
 */
class VereinskontoSeeder extends Seeder
{
    public const EMAIL = 'verein@kein-einzelfall.test';

    public const PASSWORT = 'kein-einzelfall';

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('VereinskontoSeeder übersprungen — nicht für Produktion gedacht.');

            return;
        }

        if (User::where('email', self::EMAIL)->exists()) {
            return;
        }

        User::create([
            'email' => self::EMAIL,
            'name' => 'Verein',
            'password' => Hash::make(self::PASSWORT),
            'vereinsbereich' => true,
            'email_verified_at' => now(),
        ]);

        $this->command?->info('Vereins-Konto angelegt: '.self::EMAIL.' / '.self::PASSWORT);
    }
}
