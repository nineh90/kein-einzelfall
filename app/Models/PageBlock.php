<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageBlock extends Model
{
    protected $fillable = ['page_id', 'typ', 'position', 'data'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    /**
     * Erlaubte Blocktypen — kuratiert, kein freier Page-Builder.
     * Jeder Eintrag entspricht einer Komponente unter resources/views/components/blocks/.
     *
     * Der Wert ist die Beschriftung fürs spätere Admin-Panel.
     */
    public const TYPEN = [
        'text' => 'Text',
        'text_media' => 'Text mit Bild',
        'schritte' => 'Ablauf in Schritten',
        'accordion' => 'Fragen und Antworten',
        'hinweis' => 'Hervorgehobener Hinweis',
        'hero' => 'Aufmacher',
        'quick_access' => 'Einstiegskarten',
        'topic_list' => 'Themenliste',
        'download_list' => 'Dokumente',
        'cta_band' => 'Hinweisband',
        'contact_close' => 'Kontakt-Abschluss',
        'contact_form' => 'Kontaktformular',
        'donation_options' => 'Spendenmöglichkeiten',
        'embed' => 'Eingebetteter Inhalt (2-Klick)',
        'hilfe_box' => 'Hilfe-Nummern',
        'inhalts_hinweis' => 'Inhaltshinweis',
        'leichte_sprache' => 'Leichte Sprache',
        'stat_strip' => 'Kennzahlen',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** Blade-Komponente zu diesem Blocktyp, z.B. "download_list" → "blocks.download-list". */
    public function komponente(): string
    {
        return 'blocks.'.str_replace('_', '-', $this->typ);
    }

    /**
     * Sprungziel für Inhaltsverzeichnis und Deep-Links.
     *
     * Leitet sich aus dem Titel ab, nicht aus der ID — dadurch bleiben geteilte
     * Links auch dann gültig, wenn die Seite neu eingepflegt wird und die
     * Datensätze neue IDs bekommen.
     */
    public function anker(): ?string
    {
        $titel = $this->data['titel'] ?? null;

        return $titel ? 'abschnitt-'.\Illuminate\Support\Str::slug($titel) : null;
    }
}
