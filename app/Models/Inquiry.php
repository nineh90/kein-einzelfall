<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eine Anfrage aus dem Kontaktformular.
 *
 * Name, E-Mail, Betreff und Nachricht liegen verschlüsselt in der Datenbank.
 * Wer einen Datenbank-Abzug in die Hände bekommt — Backup, Hoster-Panel,
 * fehlkonfiguriertes phpMyAdmin — sieht dort keinen Klartext.
 *
 * Der Preis: Auf diesen Feldern ist kein WHERE, kein LIKE und kein ORDER BY
 * möglich. Für eine Anfragenverwaltung ist das verschmerzbar; die Liste im
 * Panel sortiert nach Eingangsdatum und Status.
 */
class Inquiry extends Model
{
    public const STATUS = [
        'offen' => 'Offen',
        'in_bearbeitung' => 'In Bearbeitung',
        'erledigt' => 'Erledigt',
    ];

    protected $fillable = [
        'name', 'email', 'betreff', 'nachricht', 'herkunft', 'status', 'erledigt_at',
    ];

    protected function casts(): array
    {
        return [
            // Laravels 'encrypted' nutzt APP_KEY. Geht der Schlüssel verloren,
            // sind diese Inhalte unwiederbringlich weg — APP_KEY gehört deshalb
            // in die Sicherung des Servers.
            'name' => 'encrypted',
            'email' => 'encrypted',
            'betreff' => 'encrypted',
            'nachricht' => 'encrypted',
            'erledigt_at' => 'datetime',
        ];
    }

    /** Anfragen ohne Absenderangabe — wir können hier nicht antworten. */
    public function istAnonym(): bool
    {
        return blank($this->email);
    }

    public function scopeOffen($query)
    {
        return $query->where('status', 'offen');
    }
}
