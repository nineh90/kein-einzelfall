<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = [
        'category_id', 'slug', 'titel', 'teaser', 'inhalt',
        'bild_pfad', 'bild_alt', 'meta_title', 'meta_description', 'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeVeroeffentlicht(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * Suche über Titel, Anriss und Fliesstext.
     *
     * Bewusst LIKE statt des MySQL-Volltextindex. Der Index klingt zunächst
     * naheliegender, bringt hier aber drei Nachteile ohne echten Gegenwert:
     *
     *  - Er greift erst ab vier Zeichen. „OEG" — ausgerechnet eines der
     *    wichtigsten Kürzel dieser Seite — fände er nicht.
     *  - Stoppwörter werden stillschweigend ignoriert.
     *  - Er sieht keine Daten aus offenen Transaktionen. Da Laravel jeden Test
     *    in eine Transaktion legt (auch mit DatabaseTruncation), liesse sich die
     *    Suche gar nicht prüfen. Ungetestete Suche in einer Seite, auf der
     *    Menschen nach Hilfe suchen, ist keine gute Idee.
     *
     * LIKE erzwingt einen vollständigen Tabellendurchlauf — bei einem
     * Vereinsblog mit einigen hundert Beiträgen ist das nicht messbar. Sollte
     * der Bestand jemals in die Zehntausende gehen, ist der Volltextindex
     * (oder ein eigener Suchdienst) der richtige Schritt.
     */
    public function scopeSuche(Builder $query, ?string $begriff): Builder
    {
        $begriff = trim((string) $begriff);

        if ($begriff === '') {
            return $query;
        }

        // Platzhalter maskieren, sonst wäre "%" eine Suche nach allem.
        $muster = '%'.addcslashes($begriff, '%_\\').'%';

        return $query->where(fn ($q) => $q
            ->where('titel', 'like', $muster)
            ->orWhere('teaser', 'like', $muster)
            ->orWhere('inhalt', 'like', $muster));
    }

    /** Kurzfassung für Übersichtslisten — nimmt den Teaser, sonst den Textanfang. */
    public function anriss(int $zeichen = 180): string
    {
        return \Illuminate\Support\Str::limit(
            $this->teaser ?: strip_tags($this->inhalt),
            $zeichen
        );
    }

    public function seiteTitel(): string
    {
        return $this->meta_title ?: $this->titel.' - Kein Einzelfall e.V.';
    }
}
