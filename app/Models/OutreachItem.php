<?php

namespace App\Models;

use App\Models\Concerns\Uebersetzbar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Ein Eintrag der Öffentlichkeitsarbeit (KEV-134): eine Messe, später auch
 * Petitionen, Presseartikel, Interviews.
 *
 * Wie die Gruppen: Auf der Übersicht /oeffentlichkeitsarbeit steht nur eine
 * kleine Karte (Baustein „Öffentlichkeitsarbeit“, je Art ein Abschnitt), der
 * ganze Text mit allen Fotos auf der eigenen Seite. Der Verein legt Einträge
 * im Panel unter „Öffentlichkeitsarbeit“ an.
 */
class OutreachItem extends Model
{
    use Uebersetzbar;

    /** Sprachfassungen (Uebersetzbar). Nicht `art`: danach wird gefiltert. */
    protected array $uebersetzbar = ['titel', 'teaser', 'beschreibung', 'ort', 'link_text'];

    /**
     * Arten. Die Beschriftung steht als Schild auf der Seite und wird mit
     * __() übersetzt (lang/en.json).
     */
    public const ARTEN = [
        'messe' => 'Messe',
        // KEV-135: eigene Petitionen und solche anderer, die der Verein unterstützt
        'petition' => 'Eigene Petition',
        'petition_unterstuetzt' => 'Unterstützte Petition',
        'presse' => 'Presseartikel',
        'interview' => 'Interview',
        'veranstaltung' => 'Veranstaltung',
        'aktion' => 'Aktion',
    ];

    protected $fillable = [
        'slug', 'art', 'titel', 'datum', 'ort', 'teaser', 'beschreibung', 'fotos',
        'link_url', 'link_text', 'uebersetzungen', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'datum' => 'date',
            'fotos' => 'array',
            'uebersetzungen' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function scopeVeroeffentlicht(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /** Eine Art oder mehrere: Der Abschnitt „Presse“ zeigt Presseartikel und Interviews (KEV-136). */
    public function scopeVonArt(Builder $query, string|array $art): Builder
    {
        return $query->whereIn('art', (array) $art);
    }

    /** Neueste zuerst; ohne Datum (etwa Petitionen) am Ende, nach Titel. */
    public function scopeNeuesteZuerst(Builder $query): Builder
    {
        return $query->orderByRaw('datum IS NULL')->orderByDesc('datum')->orderBy('titel');
    }

    public function url(?string $sprache = null): string
    {
        return sprachlink('oeffentlichkeitsarbeit.show', ['slug' => $this->slug], $sprache);
    }

    public function artName(): ?string
    {
        return isset(self::ARTEN[$this->art]) ? __(self::ARTEN[$this->art]) : null;
    }

    /**
     * Die Fotos mit Bild, in der Reihenfolge aus dem Panel.
     *
     * @return list<array{bild: string, alt: string}>
     */
    public function fotoListe(): array
    {
        // Bildbeschreibungen anderer Sprachen je Foto, in derselben
        // Reihenfolge: {"en": {"foto_alt": ["…", "…"]}}. Die Fotos selbst
        // gelten für jede Sprache.
        $uebersetzt = app()->isLocale(Language::standardCode())
            ? []
            : ($this->uebersetzungen[app()->getLocale()]['foto_alt'] ?? []);

        return collect($this->fotos ?? [])
            ->filter(fn ($f) => is_array($f) && filled($f['bild'] ?? null))
            ->values()
            ->map(fn ($f, $i) => [
                'bild' => $f['bild'],
                'alt' => (string) (filled($uebersetzt[$i] ?? null) ? $uebersetzt[$i] : ($f['alt'] ?? '')),
            ])
            ->all();
    }

    /** Das erste Foto steht auf der Karte und oben auf der Seite. */
    public function titelfoto(): ?array
    {
        return $this->fotoListe()[0] ?? null;
    }

    /**
     * Kurztext für die Karte: der gepflegte, sonst der erste Absatz des
     * Texts. Gekürzt wird auf der Karte per CSS (drei Zeilen), nicht hier:
     * Ein Schnitt nach Zeichen landete mitten im Wort.
     */
    public function anriss(): string
    {
        if (filled($this->teaser)) {
            return $this->teaser;
        }

        $absatz = preg_split('#</p>#i', (string) $this->beschreibung)[0] ?? '';

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($absatz))));
    }

    /** „10.10.2026“, auf Englisch „10 October 2026“. */
    public function datumKurz(): ?string
    {
        return $this->datum?->locale(app()->getLocale())->translatedFormat(app()->isLocale('en') ? 'j F Y' : 'd.m.Y');
    }

    /** „10. Oktober 2026“, auf Englisch „10 October 2026“. */
    public function datumLang(): ?string
    {
        return $this->datum?->locale(app()->getLocale())->translatedFormat(app()->isLocale('en') ? 'j F Y' : 'j. F Y');
    }
}
