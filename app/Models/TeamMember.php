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

    /**
     * Wie die Person im Knopf „Mehr über … lesen“ heisst.
     *
     * Normalerweise der Vorname („Mehr über Tatjana lesen“). Das erste Wort
     * passt aber nicht immer: Aus „Herr und Frau Unbekannt“ wurde „Mehr über
     * Herr lesen“. Beginnt der Name mit einer Anrede oder nennt er mehrere
     * Personen („und“, „&“), steht deshalb der ganze Name da.
     */
    public function rufname(): string
    {
        $name = trim($this->name);
        $anrede = preg_match('/^(Herr|Frau|Dr\.|Prof\.)\s/u', $name);
        $mehrere = preg_match('/\s(und|&)\s/u', $name);

        return $anrede || $mehrere ? $name : Str::before($name, ' ');
    }

    /** Sprungziel, damit sich einzelne Profile verlinken lassen. */
    public function anker(): string
    {
        return 'person-'.Str::slug($this->name);
    }
}
