<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TeamMember extends Model
{
    protected $fillable = [
        'name', 'rolle', 'untertitel', 'kurzprofil', 'profil',
        'foto_pfad', 'foto_alt', 'bereich', 'position', 'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function scopeVeroeffentlicht(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /** Hat diese Person einen ausführlichen Text hinterlegt? */
    public function hatProfil(): bool
    {
        return filled($this->profil);
    }

    /** Sprungziel, damit sich einzelne Profile verlinken lassen. */
    public function anker(): string
    {
        return 'person-'.Str::slug($this->name);
    }
}
