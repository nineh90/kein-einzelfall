<?php

namespace App\Models;

use App\Support\Seitenkontext;
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
        // KEV-98: zwei Wege nebeneinander, darunter ein Formular mit Auswahl
        // (Beschwerdemanagement: Kritik von außen, Beschwerde über den Verein).
        'formular_spalten' => 'Zwei Wege mit gemeinsamem Formular',
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
        // KEV-134: Messen, Petitionen, Presse … als kleine Karten, je Art ein
        // Abschnitt. Die Einträge selbst pflegt der Verein unter
        // „Öffentlichkeitsarbeit“ im Panel (App\Models\OutreachItem), jeder
        // hat eine eigene Seite.
        'oeffentlichkeitsarbeit' => 'Öffentlichkeitsarbeit (Messen, Petitionen, Presse …)',
        // KEV-157: Sätze, die man nacheinander liest; jeder setzt einen
        // Zählstrich an die Wand.
        'wall_of_shame' => 'Wall of Shame (Sätze mit Zählstrichen)',
        // KEV-156: die Seiten unter diesem Bereich im Menü, mit Titelbild,
        // Unterzeile und auf Wunsch den Aufrufen. Nichts davon wird hier
        // gepflegt, es kommt aus Menü und Seiten (App\Support\Unterseiten).
        'unterseiten' => 'Unterseiten als Kacheln (mit Bild und Aufrufen)',
        // KEV-155: die Glossar-Einträge, die im Panel dieser Seite
        // zugeordnet sind, mit Suche und Buchstabenleiste.
        'glossar' => 'Glossar (Einträge dieser Seite)',
        // KEV-262: Sätze aus Bescheiden, Urteilen und Gutachten mit ihrer
        // einfachen Übersetzung, unter dem Glossar ihrer Kategorie.
        'typische_saetze' => 'Typische Sätze – einfach übersetzt',
        // KEV-152: die Fristen aus dem Panel mit Suche, A–Z und
        // aufklappbaren Karten.
        'fristen' => 'Fristenübersicht (Suche, A–Z, aufklappbar)',
        // KEV-224: Veranstaltungen und Gruppentreffen eines Monats, aus
        // denselben Daten wie /veranstaltungen (App\Support\Termine).
        'kalender' => 'Termin-Kalender (Veranstaltungen und Gruppentreffen)',
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
        'formular_spalten' => 'wechselnd',
        'donation_options' => 'wechselnd',
        'hilfe_box' => 'wechselnd',
        'partner_logos' => 'wechselnd',
        'speicher_uebersicht' => 'wechselnd',
        'oeffentlichkeitsarbeit' => 'wechselnd',
        'wall_of_shame' => 'wechselnd',
        'unterseiten' => 'wechselnd',
        'glossar' => 'wechselnd',
        'typische_saetze' => 'wechselnd',
        'fristen' => 'wechselnd',
        'kalender' => 'wechselnd',
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

    /** Bausteine, die rechts neben einem Textabschnitt stehen können (KEV-226, KEV-224). */
    public const DANEBEN = ['download_list', 'kalender'];

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
     * @return list<array{bloecke: list<self>, flaeche: string, daneben?: self}>
     */
    public static function abschnitte(iterable $bloecke, string $davor = 'card'): array
    {
        $abschnitte = [];

        foreach ($bloecke as $block) {
            $letzter = array_key_last($abschnitte);

            // Dokumente neben dem Text (KEV-226): Steht direkt darüber genau
            // ein Textabschnitt, wandert die Liste in dessen Abschnitt.
            // Seit KEV-224 auch der Termin-Kalender.
            if (in_array($block->typ, self::DANEBEN, true) && ! empty($block->data['neben_text']) && $letzter !== null
                && count($abschnitte[$letzter]['bloecke']) === 1
                && $abschnitte[$letzter]['bloecke'][0]->typ === 'text'
                && ! isset($abschnitte[$letzter]['daneben'])) {
                $abschnitte[$letzter]['daneben'] = $block;

                continue;
            }

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
            'mittig' => (bool) ($data['mittig'] ?? false),
        ];
    }

    /**
     * Ob der Baustein auf der Seite gar nichts zeigt. Dann fällt er aus der
     * Seite heraus, bevor die Flächen verteilt werden: Ein leerer Baustein
     * nähme sonst eine Fläche ein, die niemand sieht, und zwei gleiche
     * Flächen stünden direkt untereinander.
     *
     * Bisher nur „Öffentlichkeitsarbeit“ ohne veröffentlichte Einträge seiner
     * Art: Der Abschnitt „Petitionen“ soll schon bereitstehen dürfen, bevor
     * es eine gibt.
     */
    public function zeigtNichts(): bool
    {
        // KEV-150: eine Dokumentenliste, in der noch nichts steht (die
        // Formulare kommen später). Sonst stünde ihre Überschrift über nichts.
        if ($this->typ === 'download_list') {
            return collect($this->data['dokumente'] ?? [])
                ->filter(fn ($d) => filled($d['datei'] ?? null) || filled($d['url'] ?? null))
                ->isEmpty();
        }

        // KEV-156: Kacheln auf einer Seite ohne Unterseiten im Menü.
        if ($this->typ === 'unterseiten') {
            return $this->page === null
                || Seitenkontext::fuer($this->page->slug)->geschwister(PHP_INT_MAX) === [];
        }

        if ($this->typ !== 'oeffentlichkeitsarbeit') {
            return false;
        }

        $arten = collect(self::oeffentlichkeitsarbeitGruppen($this->data ?? []))->pluck('arten')->flatten()->unique()->all();

        return ! OutreachItem::veroeffentlicht()->vonArt($arten)->exists();
    }

    /**
     * Die Gruppen eines Abschnitts „Öffentlichkeitsarbeit“: seit KEV-135 die
     * gepflegten Untergruppen (Petitionen: „Initiierte“ und „Unterstützte“),
     * sonst eine Gruppe ohne eigene Überschrift aus den Arten des Abschnitts.
     *
     * @param  array<string, mixed>  $data
     * @return list<array{titel: ?string, einleitung: ?string, arten: list<string>}>
     */
    public static function oeffentlichkeitsarbeitGruppen(array $data): array
    {
        $gruppen = collect($data['gruppen'] ?? [])
            ->filter(fn ($g) => is_array($g) && filled($g['arten'] ?? null))
            ->map(fn ($g) => [
                'titel' => $g['titel'] ?? null,
                'einleitung' => $g['einleitung'] ?? null,
                'arten' => array_values((array) $g['arten']),
            ])
            ->values()
            ->all();

        return $gruppen ?: [[
            'titel' => null,
            'einleitung' => null,
            'arten' => self::oeffentlichkeitsarbeitArten($data),
        ]];
    }

    /**
     * Welche Arten ein Abschnitt „Öffentlichkeitsarbeit“ zeigt. Seit KEV-136
     * eine Liste unter `arten`; bis dahin ein einzelner Schlüssel unter `art`
     * (die Migration stellt um, gelesen wird beides).
     *
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    public static function oeffentlichkeitsarbeitArten(array $data): array
    {
        return array_values((array) ($data['arten'] ?? $data['art'] ?? 'messe'));
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

        if (! $titel) {
            return null;
        }

        // Fest vergeben (KEV-152): bleibt gleich, wenn die Überschrift sich
        // ändert oder übersetzt wird. Links wie /fristen#moegliche-fristen
        // zeigen dann in jeder Sprache auf denselben Abschnitt.
        return filled($this->data['anker'] ?? null)
            ? $this->data['anker']
            : 'abschnitt-'.Str::slug($titel);
    }
}
