<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eine Anmeldung zu einer Selbsthilfegruppe (KEV-226).
 *
 * Schon dass es sie gibt, ist eine Gesundheitsangabe (Art. 9 DSGVO): Name
 * und E-Mail liegen deshalb verschlüsselt in der Datenbank, wie bei den
 * Anfragen (App\Models\Inquiry), und werden nach Frist gelöscht
 * (`anmeldungen:aufraeumen`, config/anmeldungen.php). Kein WHERE und kein
 * ORDER BY auf diesen Feldern, die Liste sortiert nach Eingang und Stand.
 */
class GroupRegistration extends Model
{
    public const STATUS = [
        'offen' => 'Wartet auf Freigabe',
        'freigegeben' => 'Freigegeben, Link verschickt',
        'abgelehnt' => 'Abgelehnt',
    ];

    protected $fillable = [
        'group_id', 'name', 'email', 'locale', 'status',
        'zugestimmt_at', 'bearbeitet_at', 'link_versendet_at',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'encrypted',
            'email' => 'encrypted',
            'zugestimmt_at' => 'datetime',
            'bearbeitet_at' => 'datetime',
            'link_versendet_at' => 'datetime',
        ];
    }

    /**
     * Ab „bearbeitet_at“ läuft die Löschfrist. Es passt immer zum Stand,
     * egal über welchen Weg er sich ändert (wie Inquiry::booted).
     */
    protected static function booted(): void
    {
        static::saving(function (self $anmeldung) {
            if ($anmeldung->status === 'offen') {
                $anmeldung->bearbeitet_at = null;
            } elseif (! $anmeldung->bearbeitet_at || $anmeldung->bearbeitet_at->isFuture()) {
                $anmeldung->bearbeitet_at = now();
            }
        });
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function scopeOffen($query)
    {
        return $query->where('status', 'offen');
    }
}
