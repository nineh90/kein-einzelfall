<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Ein Eintrag im Glossar: eine Abkürzung oder ein Fachbegriff mit Erklärung.
 *
 * Siehe die Migration `create_glossary_terms_table` für die Begründung, warum
 * das eine eigene Tabelle ist und keine Inhaltsseite mit Aufklappern.
 */
class GlossaryTerm extends Model
{
    protected $fillable = [
        'locale', 'uebersetzungs_gruppe', 'slug', 'kuerzel', 'begriff',
        'erklaerung', 'mehr_url', 'mehr_label', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /** Dasselbe Muster wie bei `pages`: keine Seite ohne Sprache und Gruppe. */
    protected static function booted(): void
    {
        static::creating(function (self $eintrag) {
            $eintrag->locale ??= Language::standardCode();
            $eintrag->uebersetzungs_gruppe ??= (string) Str::ulid();
            $eintrag->slug ??= Str::slug($eintrag->kuerzel ?: $eintrag->begriff);
        });
    }

    public function scopeVeroeffentlicht(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * Wie der Eintrag in der Liste steht: „GdB — Grad der Behinderung“.
     *
     * Ohne Abkürzung nur der Begriff. Ein Gedankenstrich ohne etwas davor sähe
     * nach einem Fehler aus.
     */
    public function ueberschrift(): string
    {
        return $this->kuerzel
            ? $this->kuerzel.' — '.$this->begriff
            : $this->begriff;
    }

    /**
     * Der Buchstabe, unter dem der Eintrag einsortiert wird.
     *
     * Nach der Abkürzung, falls es eine gibt: Wer „GdB“ im Bescheid liest,
     * sucht unter G und nicht unter „Grad der Behinderung“ — auch wenn beides
     * hier zufällig zusammenfällt. Bei „SGB XIV“ und „Sozialgesetzbuch“ tut es
     * das nicht mehr.
     *
     * Umlaute werden auf ihren Grundbuchstaben abgebildet: Ein eigenes Fach für
     * „Ü“ zwischen „U“ und „V“ ist im Deutschen unüblich und lässt Einträge
     * verschwinden, die man dort nicht sucht.
     */
    public function anfangsbuchstabe(): string
    {
        $wort = $this->kuerzel ?: $this->begriff;
        $erster = mb_strtoupper(mb_substr(Str::ascii($wort), 0, 1));

        // Zahlen und Sonderzeichen landen gesammelt vorn.
        return preg_match('/[A-Z]/', $erster) ? $erster : '#';
    }

    /**
     * Alle veröffentlichten Einträge einer Sprache, nach Buchstaben gebündelt.
     *
     * Die Sortierung läuft über PHPs Collator und nicht über die Datenbank:
     * MySQL sortiert je nach Kollation „Ö“ hinter „Z“, und ein Glossar, in dem
     * „Örtliche Zuständigkeit“ am Ende steht, findet niemand.
     *
     * @return Collection<string, Collection<int, self>>
     */
    public static function nachBuchstaben(string $locale): Collection
    {
        $eintraege = self::veroeffentlicht()
            ->where('locale', $locale)
            ->get()
            ->sortBy(
                fn (self $e) => Str::ascii($e->kuerzel ?: $e->begriff),
                SORT_NATURAL | SORT_FLAG_CASE
            );

        return $eintraege->groupBy(fn (self $e) => $e->anfangsbuchstabe());
    }
}
