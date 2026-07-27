<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    public const TYPEN = [
        'selbsthilfe' => 'Selbsthilfegruppe',
        'arbeits' => 'Arbeitsgruppe',
    ];

    public const STATUS = [
        'offen' => 'Offen für neue Teilnehmende',
        'geplant' => 'In Planung',
        'geschlossen' => 'Zurzeit geschlossen',
    ];

    protected $fillable = [
        'slug', 'name', 'kuerzel', 'typ', 'teaser', 'beschreibung',
        'rhythmus', 'uhrzeit', 'ort', 'online', 'status',
        'anmeldung_hinweis', 'position', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'online' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopeVeroeffentlicht(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeVomTyp(Builder $query, string $typ): Builder
    {
        return $query->where('typ', $typ);
    }

    public function istOffen(): bool
    {
        return $this->status === 'offen';
    }

    /** Termin und Ort in einer Zeile, wie auf der Altseite geschrieben. */
    public function wannUndWo(): string
    {
        return collect([
            $this->rhythmus,
            $this->uhrzeit,
            $this->online ? ($this->ort ?: 'online') : $this->ort,
        ])->filter()->implode(' · ');
    }
}
