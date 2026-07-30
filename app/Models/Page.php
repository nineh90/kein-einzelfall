<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Page extends Model
{
    /**
     * Die schwere Fassung — der Normalfall.
     */
    public const FASSUNG_STANDARD = 'standard';

    /**
     * Leichte Sprache.
     *
     * Eine *Fassung*, keine Sprache: Leichte Sprache ist Deutsch, das
     * lang-Attribut bleibt `de`. Die Begründung im Langen steht in der
     * Migration `add_fassung_to_pages_table`.
     */
    public const FASSUNG_LEICHTE_SPRACHE = 'leichte_sprache';

    /** Adresspräfix je Fassung. Die Standardfassung hat keines. */
    public const FASSUNGEN = [
        self::FASSUNG_STANDARD => '',
        self::FASSUNG_LEICHTE_SPRACHE => 'leichte-sprache',
    ];

    protected $fillable = [
        'locale', 'fassung', 'uebersetzungs_gruppe', 'slug', 'titel',
        'meta_title', 'meta_description', 'noindex', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'noindex' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Jede Seite gehört einer Übersetzungsgruppe an — auch die einsprachige.
     * Ohne Vorbelegung entstünden Seiten ohne Gruppe, die sich später nicht
     * mehr verknüpfen lassen.
     */
    protected static function booted(): void
    {
        static::creating(function (self $page) {
            $page->locale ??= Language::standardCode();
            $page->fassung ??= self::FASSUNG_STANDARD;
            $page->uebersetzungs_gruppe ??= (string) Str::ulid();
        });
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('position');
    }

    public function scopeVeroeffentlicht($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /** @param  string|null  $locale  null = die aktuell aktive Sprache */
    public function scopeSprache($query, ?string $locale = null)
    {
        return $query->where('locale', $locale ?? app()->getLocale());
    }

    /**
     * Nur die schweren Fassungen.
     *
     * Der Vorgabefall an fast jeder Stelle: Navigation, Sprachumschalter,
     * hreflang und Brotkrumen meinen immer die Standardfassung. Eine Seite in
     * Leichter Sprache, die sich in ein Menü schiebt, waere ein Fehler, den
     * niemand sucht.
     */
    public function scopeStandardfassung($query)
    {
        return $query->where('fassung', self::FASSUNG_STANDARD);
    }

    public function istLeichteSprache(): bool
    {
        return $this->fassung === self::FASSUNG_LEICHTE_SPRACHE;
    }

    /** Die Fassung in Leichter Sprache zu dieser Seite — oder null. */
    public function leichteSprache(): ?self
    {
        if ($this->istLeichteSprache()) {
            return null;
        }

        return $this->fassungInner(self::FASSUNG_LEICHTE_SPRACHE);
    }

    /** Zurueck zur schweren Fassung. */
    public function standardfassung(): ?self
    {
        if (! $this->istLeichteSprache()) {
            return null;
        }

        return $this->fassungInner(self::FASSUNG_STANDARD);
    }

    private function fassungInner(string $fassung): ?self
    {
        return self::veroeffentlicht()
            ->where('uebersetzungs_gruppe', $this->uebersetzungs_gruppe)
            ->where('locale', $this->locale)
            ->where('fassung', $fassung)
            ->first();
    }

    /** Die übrigen Sprachfassungen derselben Seite. */
    public function uebersetzungen(): HasMany
    {
        return $this->hasMany(self::class, 'uebersetzungs_gruppe', 'uebersetzungs_gruppe')
            ->whereKeyNot($this->getKey());
    }

    /** Diese Seite in einer anderen Sprache — oder null, wenn es sie nicht gibt. */
    public function inSprache(string $locale): ?self
    {
        if ($locale === $this->locale) {
            return $this;
        }

        return self::veroeffentlicht()
            ->where('uebersetzungs_gruppe', $this->uebersetzungs_gruppe)
            ->where('locale', $locale)
            // Innerhalb derselben Fassung bleiben: Die englische Uebersetzung
            // einer Seite in Leichter Sprache waere die englische Seite in
            // Leichter Sprache, nicht die schwere.
            ->where('fassung', $this->fassung)
            ->first();
    }

    public function sprache(): Language
    {
        return Language::finden($this->locale) ?? Language::standard();
    }

    /** Öffentlicher Pfad inklusive Sprach- und Fassungspräfix. */
    public function pfad(): string
    {
        $fassung = self::FASSUNGEN[$this->fassung] ?? '';

        return $this->sprache()->pfad('/'.($fassung ? $fassung.'/' : '').$this->slug);
    }

    /**
     * Titel für <title>. Folgt dem Muster der Altseite ("%Seite% - Kein Einzelfall e.V."),
     * damit sich die Suchergebnisse nach dem Umzug nicht verändern.
     */
    public function seiteTitel(): string
    {
        return $this->meta_title ?: $this->titel.' - Kein Einzelfall e.V.';
    }
}
