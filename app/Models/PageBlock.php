<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PageBlock extends Model
{
    protected $fillable = ['page_id', 'typ', 'position', 'data'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    /**
     * Leere Felder gar nicht erst speichern.
     *
     * Das Formular im Panel schickt jedes sichtbare Feld mit, auch die
     * unausgefüllten. Ohne diese Bereinigung sammelte jeder Baustein bei jedem
     * Speichern Schlüssel mit `null` an. Das ist kein Schönheitsfehler: Die
     * Bausteindaten werden von Hand gelesen und übersetzt, und jedes Speichern
     * sähe im Vergleich zweier Stände wie eine inhaltliche Änderung aus.
     *
     * `false` und `0` bleiben stehen — das sind Angaben, keine Leerstellen.
     */
    protected static function booted(): void
    {
        static::saving(function (self $block) {
            $block->data = self::ohneLeere($block->data ?? []);
        });
    }

    /**
     * @param  array<string|int, mixed>  $daten
     * @return array<string|int, mixed>
     */
    private static function ohneLeere(array $daten): array
    {
        $gefiltert = [];

        foreach ($daten as $schluessel => $wert) {
            if (is_array($wert)) {
                $wert = self::ohneLeere($wert);
            }

            if ($wert === null || $wert === '' || $wert === []) {
                continue;
            }

            $gefiltert[$schluessel] = $wert;
        }

        // Listen dürfen keine Lücken behalten: Aus einer Liste mit Loch wird in
        // JSON ein Objekt, und darüber läuft keine @foreach-Schleife mehr.
        return array_is_list($daten) ? array_values($gefiltert) : $gefiltert;
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
        'team_grid' => 'Vorstand und Team',
        'group_list' => 'Gruppen-Übersicht',
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

        return $titel ? 'abschnitt-'.Str::slug($titel) : null;
    }
}
