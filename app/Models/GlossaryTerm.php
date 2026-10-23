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
    /**
     * Die Seite des ersten Glossars. Einträge ohne Zuordnung (`seite_gruppe`
     * leer) stehen dort, so wie vor KEV-155, als es nur dieses gab.
     */
    public const HAUPTSEITE = 'glossar';

    protected $fillable = [
        'locale', 'uebersetzungs_gruppe', 'slug', 'kuerzel', 'begriff',
        'erklaerung', 'mehr_url', 'mehr_label', 'published_at',
        // KEV-155: auf welcher Seite der Eintrag steht (deren Übersetzungsgruppe).
        'seite_gruppe',
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
     * Die Einträge, die auf $seite stehen (KEV-155): zugeordnet über die
     * Übersetzungsgruppe der Seite, ohne Zuordnung im Hauptglossar.
     */
    public function scopeAufSeite(Builder $query, Page $seite): Builder
    {
        return $query->where(function (Builder $q) use ($seite) {
            $q->where('seite_gruppe', $seite->uebersetzungs_gruppe);

            if ($seite->slug === self::HAUPTSEITE) {
                $q->orWhereNull('seite_gruppe');
            }
        });
    }

    /**
     * Die Glossar-Seiten für die Auswahl im Panel: deutsche Seiten mit dem
     * Baustein „Glossar“, Übersetzungsgruppe → Titel.
     *
     * @return array<string, string>
     */
    public static function seitenAuswahl(): array
    {
        // Einmal je Aufruf: Die Spalte im Panel fragt für jede Zeile.
        return once(fn () => Page::query()
            ->where('locale', Language::standardCode())
            ->standardfassung()
            ->whereHas('blocks', fn ($q) => $q->where('typ', 'glossar'))
            ->orderBy('titel')
            ->pluck('titel', 'uebersetzungs_gruppe')
            ->all());
    }

    /** Übersetzungsgruppe der Seite „glossar“, die Vorgabe für neue Einträge. */
    public static function hauptseitenGruppe(): ?string
    {
        return Page::where('slug', self::HAUPTSEITE)->where('locale', Language::standardCode())->value('uebersetzungs_gruppe');
    }

    /**
     * Die Seite, auf der der Eintrag steht, in der Sprache $locale, sonst in
     * der Standardsprache. Für Links aus der Suche.
     */
    public function seite(string $locale): ?Page
    {
        $seiten = Page::veroeffentlicht()
            ->standardfassung()
            ->whereIn('locale', array_unique([$locale, Language::standardCode()]))
            ->when(
                $this->seite_gruppe,
                fn ($q) => $q->where('uebersetzungs_gruppe', $this->seite_gruppe),
                fn ($q) => $q->where('slug', self::HAUPTSEITE),
            )
            ->get();

        return $seiten->firstWhere('locale', $locale) ?? $seiten->first();
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
     * Text für die Suche im Glossar (KEV-153): Abkürzung, Begriff und
     * Erklärung, vereinfacht wie die Eingabe.
     */
    public function suchtext(): string
    {
        return self::vereinfacht($this->kuerzel.' '.$this->begriff.' '.$this->erklaerung);
    }

    /**
     * Passt der Eintrag zu dem, was jemand ins Suchfeld des Glossars tippt?
     *
     * Zwei Wege. Die Abkürzung ohne Leerzeichen und Punkte: So findet „idr“
     * auch „i. d. R.“ und „bt drs“ auch „BT-Drs.“. Der übrige Text mit
     * Wortgrenzen: Ohne sie fand „sgb“ auch „BSG Bundessozialgericht“
     * („bs*g b*undes…“). Dieselbe Regel steht in resources/js/glossar.js,
     * damit das Filtern beim Tippen dasselbe findet wie das Absenden ohne
     * JavaScript.
     */
    public function passt(string $suche): bool
    {
        $gesucht = self::vereinfacht($suche);

        return $gesucht === ''
            || str_contains(self::kompakt((string) $this->kuerzel), self::kompakt($gesucht))
            || str_contains($this->suchtext(), $gesucht);
    }

    /** Klein, Umlaute ausgeschrieben, sonst nur Buchstaben, Ziffern und einzelne Leerzeichen. */
    public static function vereinfacht(string $text): string
    {
        $text = strtr(mb_strtolower($text), ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss']);

        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::ascii($text)));
    }

    /** Wie vereinfacht(), aber ganz ohne Leerzeichen: „i. d. R.“ → „idr“. */
    public static function kompakt(string $text): string
    {
        return str_replace(' ', '', self::vereinfacht($text));
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
     * Alle veröffentlichten Einträge einer Sprache (und einer Seite), nach
     * Buchstaben gebündelt.
     *
     * Die Sortierung läuft über PHPs Collator und nicht über die Datenbank:
     * MySQL sortiert je nach Kollation „Ö“ hinter „Z“, und ein Glossar, in dem
     * „Örtliche Zuständigkeit“ am Ende steht, findet niemand.
     *
     * @return Collection<string, Collection<int, self>>
     */
    public static function nachBuchstaben(string $locale, ?Page $seite = null): Collection
    {
        $eintraege = self::veroeffentlicht()
            ->where('locale', $locale)
            // Ohne Seite alle: so zählt und sucht die Websuche über alle Glossare.
            ->when($seite, fn ($q) => $q->aufSeite($seite))
            ->get()
            ->sortBy(
                fn (self $e) => Str::ascii($e->kuerzel ?: $e->begriff),
                SORT_NATURAL | SORT_FLAG_CASE
            );

        return $eintraege->groupBy(fn (self $e) => $e->anfangsbuchstabe());
    }
}
