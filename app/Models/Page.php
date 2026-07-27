<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = [
        'locale', 'uebersetzungs_gruppe', 'slug', 'titel',
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
            ->first();
    }

    public function sprache(): Language
    {
        return Language::finden($this->locale) ?? Language::standard();
    }

    /** Öffentlicher Pfad inklusive Sprachpräfix. */
    public function pfad(): string
    {
        return $this->sprache()->pfad('/'.$this->slug);
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
