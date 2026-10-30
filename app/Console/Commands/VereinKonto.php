<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Legt ein Konto für den Vereinsbereich (/intern) an oder setzt sein
 * Passwort neu. Das Konto kommt nicht in die Verwaltung (/admin).
 *
 * Das Passwort wird erzeugt und genau einmal angezeigt, damit es nicht in
 * der Shell-Historie landet.
 */
class VereinKonto extends Command
{
    protected $signature = 'verein:konto {email} {name?}';

    protected $description = 'Konto für den Vereinsbereich (/intern) anlegen oder Passwort neu setzen';

    public function handle(): int
    {
        $email = Str::lower(trim($this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Das ist keine gültige E-Mail-Adresse.');

            return self::FAILURE;
        }

        $passwort = Str::password(16, symbols: false);
        $konto = User::firstOrNew(['email' => $email]);
        $neu = ! $konto->exists;

        $konto->fill([
            'name' => $this->argument('name') ?? $konto->name ?? 'Verein',
            'password' => Hash::make($passwort),
            'vereinsbereich' => true,
        ]);
        $konto->email_verified_at ??= now();
        $konto->save();

        $this->info(($neu ? 'Konto angelegt' : 'Passwort neu gesetzt').': '.$email);
        $this->line('Passwort (wird nur jetzt angezeigt): '.$passwort);
        $this->line('Anmelden unter: '.url('/intern'));

        return self::SUCCESS;
    }
}
