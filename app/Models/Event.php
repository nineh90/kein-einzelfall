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

    /**
     * Zeitangabe in einem Stück, wie sie auf der Seite erscheint.
     *
     * Deutsch: „14.10.2026, 18:00 bis 20:00 Uhr“. Englisch mit ausgeschriebenem
     * Monat und 12-Stunden-Uhr: „14 October 2026, 6:00 pm to 8:00 pm“ — die
     * Zahlenschreibweise 14.10. läse man dort leicht falsch herum.
     */
    public function zeitraum(): string
    {
        $beginn = $this->beginnt_am;
        $ende = $this->endet_am;

        [$tagFormat, $uhrFormat] = app()->isLocale('en') ? ['j F Y', 'g:i a'] : ['d.m.Y', 'H:i'];
        $datum = fn ($t) => $t->locale(app()->getLocale())->translatedFormat($tagFormat);
        $uhr = fn ($t) => $t->locale(app()->getLocale())->translatedFormat($uhrFormat);
        $datumUhr = fn ($t) => $datum($t).', '.$uhr($t);

        if ($this->ganztaegig) {
            return $ende && ! $ende->isSameDay($beginn)
                ? __(':beginn bis :ende', ['beginn' => $datum($beginn), 'ende' => $datum($ende)])
                : $datum($beginn);
        }

        if (! $ende) {
            return __(':zeit Uhr', ['zeit' => $datumUhr($beginn)]);
        }

        return __(':beginn bis :ende Uhr', [
            'beginn' => $datumUhr($beginn),
            'ende' => $ende->isSameDay($beginn) ? $uhr($ende) : $datumUhr($ende),
        ]);
    }

    /** Für Screenreader und <time datetime="…"> */
    public function zeitMaschinenlesbar(): string
    {
        return $this->ganztaegig
            ? $this->beginnt_am->toDateString()
            : $this->beginnt_am->toIso8601String();
    }
}
