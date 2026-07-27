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

    public const WIEDERHOLUNGEN = [
        'keine' => 'Kein fester Rhythmus',
        'woechentlich' => 'Wöchentlich',
        'monatlich_nter_wochentag' => 'Monatlich an einem bestimmten Wochentag',
    ];

    public const WOCHENTAGE = [
        1 => 'Montag', 2 => 'Dienstag', 3 => 'Mittwoch', 4 => 'Donnerstag',
        5 => 'Freitag', 6 => 'Samstag', 7 => 'Sonntag',
    ];

    protected $fillable = [
        'slug', 'name', 'kuerzel', 'typ', 'teaser', 'beschreibung',
        'rhythmus', 'uhrzeit', 'ort', 'online', 'status',
        'anmeldung_hinweis', 'position', 'published_at',
        'wiederholung', 'wochentag', 'woche_im_monat', 'beginn_zeit', 'dauer_minuten',
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

    /**
     * Die nächsten Termine dieser Gruppe.
     *
     * Berechnet aus den Wiederholungsfeldern. Ohne festen Rhythmus kommt eine
     * leere Liste zurück — dann steht auf der Seite nur der Freitext.
     *
     * Bewusst berechnet statt gespeichert: Ein wöchentlicher Termin würde sonst
     * unbegrenzt Datensätze erzeugen, die jemand pflegen müsste. Fällt ein
     * einzelnes Treffen aus, gehört das als Einzelveranstaltung erfasst.
     *
     * @return \Illuminate\Support\Collection<int, \Carbon\CarbonImmutable>
     */
    public function naechsteTermine(int $anzahl = 3, ?\Carbon\CarbonImmutable $ab = null): \Illuminate\Support\Collection
    {
        $ab ??= \Carbon\CarbonImmutable::now();
        $termine = collect();

        if ($this->wiederholung === 'keine' || ! $this->wochentag) {
            return $termine;
        }

        $zeit = $this->beginn_zeit
            ? \Carbon\CarbonImmutable::parse($this->beginn_zeit)
            : null;

        $setzeZeit = fn (\Carbon\CarbonImmutable $t) => $zeit
            ? $t->setTime((int) $zeit->format('H'), (int) $zeit->format('i'))
            : $t->startOfDay();

        if ($this->wiederholung === 'woechentlich') {
            // Carbon::next() zählt 0 = Sonntag bis 6 = Samstag. Gespeichert ist
            // ISO-8601 (1 = Montag bis 7 = Sonntag) — der deutsche Wochentagsname
            // funktioniert hier nicht und würde eine Ausnahme werfen.
            $naechster = $setzeZeit($ab->next(((int) $this->wochentag) % 7));

            for ($i = 0; $i < $anzahl; $i++) {
                $termine->push($naechster->addWeeks($i));
            }

            return $termine;
        }

        // monatlich, n-ter Wochentag im Monat
        $monat = $ab->startOfMonth();

        // Zwölf Monate reichen: mehr als ein Jahr im Voraus anzuzeigen hilft
        // niemandem und würde nur Termine zeigen, die noch niemand zugesagt hat.
        for ($i = 0; $i < 12 && $termine->count() < $anzahl; $i++) {
            $kandidat = $this->nterWochentagImMonat($monat->addMonths($i));

            if ($kandidat && $setzeZeit($kandidat)->greaterThanOrEqualTo($ab)) {
                $termine->push($setzeZeit($kandidat));
            }
        }

        return $termine;
    }

    private function nterWochentagImMonat(\Carbon\CarbonImmutable $monat): ?\Carbon\CarbonImmutable
    {
        $tage = collect();
        $tag = $monat->startOfMonth();

        while ($tag->month === $monat->month) {
            if ($tag->dayOfWeekIso === (int) $this->wochentag) {
                $tage->push($tag);
            }
            $tag = $tag->addDay();
        }

        // 5 bedeutet „der letzte im Monat" — manche Monate haben nur vier.
        if ($this->woche_im_monat >= 5) {
            return $tage->last();
        }

        return $tage->get(((int) $this->woche_im_monat) - 1);
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
