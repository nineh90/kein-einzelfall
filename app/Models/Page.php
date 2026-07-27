<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'slug', 'titel', 'meta_title', 'meta_description', 'noindex', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'noindex' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('position');
    }

    public function scopeVeroeffentlicht($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
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
