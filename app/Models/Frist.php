<?php

namespace App\Models;

use App\Models\Concerns\Uebersetzbar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Eine Frist der Fristenübersicht (KEV-152), siehe die Migration
 * `create_fristen_table`.
 *
 * Gesucht, sortiert und nach Buchstaben gebündelt wird wie im Glossar
 * (GlossaryTerm), damit sich beide Übersichten gleich bedienen; das Filtern
 * beim Tippen übernimmt dasselbe Skript (resources/js/glossar.js).
 */
class Frist extends Model
{
    use Uebersetzbar;

    protected $table = 'fristen';

    protected array $uebersetzbar = ['titel', 'frist', 'schlagworte', 'inhalt'];

    protected $fillable = ['slug', 'titel', 'frist', 'schlagworte', 'inhalt', 'uebersetzungen', 'published_at'];

    protected function casts(): array
    {
        return [
            'schlagworte' => 'array',
            'uebersetzungen' => 'array',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $frist) {
            $frist->slug ??= Str::slug($frist->titel);
        });
    }

    public function scopeVeroeffentlicht(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /** Titel, Frist, Schlagworte und Text, vereinfacht wie die Eingabe ins Suchfeld. */
    public function suchtext(): string
    {
        return GlossaryTerm::vereinfacht(implode(' ', [
            $this->titel,
            $this->frist,
            implode(' ', $this->schlagworte ?? []),
            html_entity_decode(strip_tags(str_replace('<', ' <', (string) $this->inhalt))),
        ]));
    }

    /**
     * Wie GlossaryTerm::passt(), nur ohne Abkürzung: auch mitten im Wort,
     * „frist“ findet „Widerspruchsfrist“. Dieselbe Regel filtert beim Tippen
     * (glossar.js).
     */
    public function passt(string $suche): bool
    {
        $gesucht = GlossaryTerm::vereinfacht($suche);

        return $gesucht === '' || str_contains($this->suchtext(), $gesucht);
    }

    /** Umlaute unter ihrem Grundbuchstaben, wie im Glossar: „Überprüfungsantrag“ unter U. */
    public function anfangsbuchstabe(): string
    {
        $erster = mb_strtoupper(mb_substr(Str::ascii((string) $this->titel), 0, 1));

        return preg_match('/[A-Z]/', $erster) ? $erster : '#';
    }

    /** @return Collection<string, Collection<int, self>> */
    public static function nachBuchstaben(): Collection
    {
        return self::veroeffentlicht()
            ->get()
            ->sortBy(fn (self $f) => Str::ascii((string) $f->titel), SORT_NATURAL | SORT_FLAG_CASE)
            ->groupBy(fn (self $f) => $f->anfangsbuchstabe());
    }
}
