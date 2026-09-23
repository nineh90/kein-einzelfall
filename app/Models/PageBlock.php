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
        // Ein Baustein für Kooperationen, Netzwerke, Förderer,
        // Schirmherrschaften und Botschafter. Sie unterscheiden sich im Text
        // darüber, nicht in der Darstellung.
        'partner_logos' => 'Partner und Unterstützer',
        // Zeigt, was die Seite im Browser ablegt, und setzt es zurück. Der
        // Inhalt kommt aus config/speicher.php und ist nicht im Panel pflegbar —
        // eine von Hand gepflegte Liste liefe der echten hinterher.
        'speicher_uebersicht' => 'Gespeicherte Einstellungen',
        'leichte_sprache' => 'Leichte Sprache',
        'stat_strip' => 'Kennzahlen',
    ];

    /**
     * Welche Fläche ein Bausteintyp einnimmt.
     *
     * Aufeinanderfolgende Abschnitte sollen sich voneinander abheben — helle
     * Seitenfläche (cream) und Kartenfläche (card) im Wechsel. Sonst laufen
     * Bausteine, die inhaltlich nichts miteinander zu tun haben, optisch
     * ineinander.
     *
     *   wechselnd     nimmt die Gegenfläche des vorigen Abschnitts
     *   anschliessend bleibt auf der Fläche des vorigen Abschnitts — für
     *                 Kästen, die zum Text davor gehören
     *   cream / card  feste Fläche; für die Nachbarn nur der Bezugspunkt
     */
    public const FLAECHEN = [
        'text' => 'wechselnd',
        'text_media' => 'wechselnd',
        'schritte' => 'wechselnd',
        'accordion' => 'wechselnd',
        'team_grid' => 'wechselnd',
        'group_list' => 'wechselnd',
        'quick_access' => 'wechselnd',
        'download_list' => 'wechselnd',
        'cta_band' => 'wechselnd',
        'contact_close' => 'wechselnd',
        'contact_form' => 'wechselnd',
        'donation_options' => 'wechselnd',
        'hilfe_box' => 'wechselnd',
        'partner_logos' => 'wechselnd',
        'speicher_uebersicht' => 'wechselnd',
        'hinweis' => 'anschliessend',
        // Der Aufmacher steht am Seitenanfang und bringt seinen eigenen
        // Verlauf mit; die Themenliste und der Kennzahlen-Streifen ihre
        // eigenen Linien.
        'hero' => 'cream',
        'topic_list' => 'card',
        'stat_strip' => 'cream',
        'embed' => 'cream',
        'inhalts_hinweis' => 'cream',
        'leichte_sprache' => 'cream',
    ];

    /**
     * Fläche für jeden Baustein einer Folge — jeweils bezogen auf das, was
     * tatsächlich davor steht, nicht auf die Positionsnummer.
     *
     * Bis September 2026 wurde stur nach Position gewechselt, und nur der
     * Textbaustein hat die Vorgabe überhaupt umgesetzt. Ergebnis: drei helle
     * Abschnitte hintereinander, oder eine Karte direkt auf der Karte des
     * Seitenkopfs.
     *
     * @param  iterable<self>  $bloecke
     * @param  string  $davor  Fläche des Elements über dem ersten Baustein
     * @return list<string>  'cream' oder 'card', in der Reihenfolge der Bausteine
     */
    public static function flaechenFuer(iterable $bloecke, string $davor = 'card'): array
    {
        $flaechen = [];

        foreach ($bloecke as $block) {
            $flaechen[] = $davor = $block->flaeche($davor);
        }

        return $flaechen;
    }

    /**
     * Die Bausteine einer Seite als Abschnitte: Aufeinanderfolgende
     * Textbausteine bilden einen gemeinsamen Abschnitt, jeder andere
     * Baustein einen eigenen.
     *
     * Vorher war jeder Textbaustein ein eigenes Band mit eigener Fläche und
     * Linien. Zwei davon hintereinander sahen aus wie zwei leere Kästen,
     * egal wie man den Text darin anordnete (Abnahme 23.09.2026). Jetzt
     * zeigt die Seite sie als einen Artikel (x-blocks.artikel) bzw. auf der
     * Startseite als Spalten (x-blocks.nebeneinander). Im Panel bleiben es
     * einzelne Bausteine.
     *
     * Die Fläche wechselt von Abschnitt zu Abschnitt, genau wie vorher von
     * Baustein zu Baustein (flaechenFuer).
     *
     * @param  iterable<self>  $bloecke
     * @param  string  $davor  Fläche des Elements über dem ersten Baustein
     * @return list<array{bloecke: list<self>, flaeche: string}>
     */
    public static function abschnitte(iterable $bloecke, string $davor = 'card'): array
    {
        $abschnitte = [];

        foreach ($bloecke as $block) {
            $letzter = array_key_last($abschnitte);

            if ($block->typ === 'text' && $letzter !== null
                && $abschnitte[$letzter]['bloecke'][0]->typ === 'text') {
                $abschnitte[$letzter]['bloecke'][] = $block;

                continue;
            }

            $davor = $block->flaeche($davor);
            $abschnitte[] = ['bloecke' => [$block], 'flaeche' => $davor];
        }

        return $abschnitte;
    }

    /**
     * Kurz genug, um auf der Startseite neben einem anderen Abschnitt zu
     * stehen: höchstens drei Absätze. Längere Texte in einer Drittel- oder
     * halben Spalte liefen zu weit nach unten.
     */
    public function istKurzerText(): bool
    {
        return $this->typ === 'text' && count($this->data['absaetze'] ?? []) <= 3;
    }

    /** Die Angaben eines Textbausteins für x-blocks.text-inhalt. */
    public function textAngaben(): array
    {
        $data = $this->data ?? [];

        return [
            'eyebrow' => $data['eyebrow'] ?? null,
            'titel' => $data['titel'] ?? null,
            'anker' => $this->anker(),
            'absaetze' => $data['absaetze'] ?? [],
            'hand' => $data['hand'] ?? null,
            'cta' => knoepfe([$data['cta'] ?? null])[0] ?? null,
        ];
    }

    /** Die Fläche dieses Bausteins, wenn davor die Fläche $davor steht. */
    public function flaeche(string $davor): string
    {
        $art = self::FLAECHEN[$this->typ] ?? 'cream';

        return match ($art) {
            // Eine im Baustein hinterlegte Fläche hat Vorrang
            'wechselnd' => $this->data['auf'] ?? self::gegenflaeche($davor),
            'anschliessend' => $davor,
            default => $art,
        };
    }

    /** Ob der Baustein die Fläche von der Seite entgegennimmt (Prop `auf`). */
    public function nimmtFlaeche(): bool
    {
        return in_array(self::FLAECHEN[$this->typ] ?? null, ['wechselnd', 'anschliessend'], true);
    }

    public static function gegenflaeche(string $flaeche): string
    {
        return $flaeche === 'card' ? 'cream' : 'card';
    }

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
