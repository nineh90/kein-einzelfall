<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'slug', 'titel', 'teaser', 'beschreibung',
        'beginnt_am', 'endet_am', 'ganztaegig', 'art',
        'ort', 'adresse', 'online', 'anmeldung_url', 'anmeldung_hinweis',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'beginnt_am' => 'datetime',
            'endet_am' => 'datetime',
            'ganztaegig' => 'boolean',
            'online' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopeVeroeffentlicht(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * Kommende Termine.
     *
     * Massgeblich ist das Ende, nicht der Beginn: Eine dreitägige Veranstaltung
     * soll am zweiten Tag noch als laufend erscheinen und nicht schon in der
     * Vergangenheit landen.
     */
    public function scopeKommend(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('endet_am', '>=', now())
                ->orWhere(fn ($q2) => $q2->whereNull('endet_am')->where('beginnt_am', '>=', now()));
        });
    }

    public function scopeVergangen(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('endet_am', '<', now())
                ->orWhere(fn ($q2) => $q2->whereNull('endet_am')->where('beginnt_am', '<', now()));
        });
    }

    public function laeuftGerade(): bool
    {
        return $this->beginnt_am->isPast()
            && $this->endet_am?->isFuture() === true;
    }

    /** Zeitangabe in einem Stück, wie sie auf der Seite erscheint. */
    public function zeitraum(): string
    {
        $beginn = $this->beginnt_am;
        $ende = $this->endet_am;

        if ($this->ganztaegig) {
            return $ende && ! $ende->isSameDay($beginn)
                ? $beginn->format('d.m.Y').' bis '.$ende->format('d.m.Y')
                : $beginn->format('d.m.Y');
        }

        if (! $ende) {
            return $beginn->format('d.m.Y, H:i').' Uhr';
        }

        return $ende->isSameDay($beginn)
            ? $beginn->format('d.m.Y, H:i').' bis '.$ende->format('H:i').' Uhr'
            : $beginn->format('d.m.Y, H:i').' bis '.$ende->format('d.m.Y, H:i').' Uhr';
    }

    /** Für Screenreader und <time datetime="…"> */
    public function zeitMaschinenlesbar(): string
    {
        return $this->ganztaegig
            ? $this->beginnt_am->toDateString()
            : $this->beginnt_am->toIso8601String();
    }
}
