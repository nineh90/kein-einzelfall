<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'panel_zugang',
        'vereinsbereich',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Standardwerte auf Model-Ebene, nicht nur als Spalten-Default.
     * Sonst ist panel_zugang direkt nach create() noch null und eine Prüfung
     * wie `=== false` liefe ins Leere.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'panel_zugang' => false,
        'vereinsbereich' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'panel_zugang' => 'boolean',
            'vereinsbereich' => 'boolean',
        ];
    }

    /**
     * Zugang zur Verwaltung — ausdrücklich statt stillschweigend.
     *
     * Filament würde ohne diese Methode ausserhalb der lokalen Umgebung jedem
     * angemeldeten Konto den Zugang verweigern oder gewähren, je nach Version.
     * Beides wollen wir nicht dem Zufall überlassen: Sobald Vereinsmitglieder
     * eigene Konten bekommen, liegen sie in derselben Tabelle wie die Redaktion.
     *
     * Der Vereinsbereich (/intern) hat ein eigenes Kennzeichen. Wer in die
     * Verwaltung darf, darf auch dorthin, umgekehrt nicht.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'verein' => $this->vereinsbereich === true || $this->panel_zugang === true,
            default => $this->panel_zugang === true,
        };
    }
}
