<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Englische Fassungen aller Seiten, des Glossars, der Gruppen und des Teams.
 *
 * Seit 08.10.2026 vollständig und per Migration ausgeliefert (Kevin: „wenn
 * das möglich ist, sollte das auch gemacht werden“). Vorher nur fünf
 * Kernseiten und nur von Hand. Die Absicherung: Jede so angelegte Seite ist
 * `ungeprueft` (sichtbarer Vermerk „Machine translation — not yet reviewed“)
 * und `noindex`. Neu aufgebaut wird eine englische Seite nur, solange sie
 * ungeprüft ist; entfernt der Verein den Haken, gehört sie ihm.
 * Glossar, Gruppen und Team werden nur befüllt, wo noch nichts steht.
 *
 * Der Text darunter beschreibt den Stand vom 31.07.2026.
 *
 * ---
 *
 * Englische Fassungen der Kernseiten — für die Vorführung.
 *
 * ⚠️ MASCHINELLE ÜBERSETZUNG. Die Texte in database/seeders/data/uebersetzungen.json
 * sind ein Entwurf, damit der Sprachumschalter etwas zu zeigen hat. Der Verein
 * prüft und korrigiert sie anschliessend im Panel.
 *
 * Russisch gab es hier bis zum 19.09.2026 ebenfalls — gestrichen, weil es
 * niemand gegenlesen konnte (Migration `russisch_entfernen`).
 *
 * Deshalb läuft dieser Seeder **nicht** automatisch beim Deploy und hängt an
 * keiner Migration. Er wird von Hand ausgeführt, bewusst nur auf der Demo-
 * Datenbank:
 *
 *     php artisan db:seed --class=UebersetzungenSeeder
 *
 * So landet kein ungeprüfter Text versehentlich auf dem Server. Solange eine
 * Seite hier fehlt, greift der eingebaute, sichtbare Rückfall: Der Besucher
 * sieht die deutsche Fassung mit einem Hinweis in seiner Sprache.
 *
 * Wie es arbeitet: Jede deutsche Seite wird geklont — gleiche Bausteine,
 * gleiche Reihenfolge, gleiche Adresse mit Sprachpräfix. Übersetzt werden nur
 * die Textfelder, und nur, wenn das Wörterbuch den deutschen Satz kennt.
 * E-Mail-Adressen, IBAN, Icons, Links und Schalter bleiben unangetastet — ein
 * übersetzter Link wäre ein toter Link. Notrufnummern werden nicht erfunden;
 * die deutschen Nummern gelten in Deutschland unabhängig von der Sprache.
 */
class UebersetzungenSeeder extends Seeder
{
    /**
     * Felder, die sichtbaren Text tragen. Nur diese werden übersetzt — `url`,
     * `icon`, `variant`, `iban` und so weiter bleiben, wie sie sind.
     */
    private const TEXT_FELDER = [
        'titel', 'sub', 'eyebrow', 'text', 'hand', 'einleitung',
        'zitat', 'notiz', 'hinweis', 'thema', 'alleLabel', 'label',
        'link', 'bild_alt',
        // Seit 08.10.2026 (alle Seiten): Knöpfe der Beschwerdeseite, Fragen und
        // Antworten, Partner, Teaser, Quellenangaben der Dokumentenliste.
        'knopf', 'frage', 'antwort', 'name', 'rolle', 'beschreibung', 'teaser', 'quelle',
    ];

    /** Pfade, die Dateien sind und kein Sprachpräfix bekommen. */
    private const DATEIPFADE = '#^/(dokumente|img|wp-content|storage|build|fonts|admin)(/|$)#';

    /** @var array<string, array{en: string}> */
    private array $woerterbuch = [];

    public function run(): void
    {
        // Die Sprachzeilen müssen da sein — auf frischer Datenbank sonst nicht.
        $this->call(SprachenSeeder::class);

        $this->woerterbuch = json_decode(
            file_get_contents(base_path('database/seeders/data/uebersetzungen.json')),
            true,
        ) ?? [];

        $angelegt = 0;

        $deutsche = Page::query()
            ->where('locale', Language::standardCode())
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->whereNotNull('published_at')
            ->with('blocks')
            ->get();

        foreach ($deutsche as $deutsch) {
            foreach (['en'] as $locale) {
                $vorhanden = Page::query()
                    ->where('locale', $locale)
                    ->where('fassung', Page::FASSUNG_STANDARD)
                    ->where('uebersetzungs_gruppe', $deutsch->uebersetzungs_gruppe)
                    ->get();

                // Geprüft (Haken entfernt): gehört dem Verein, nicht anfassen.
                if ($vorhanden->contains(fn (Page $p) => ! $p->ungeprueft)) {
                    continue;
                }

                // Ungeprüft: neu aufbauen, damit Korrekturen am Wörterbuch ankommen.
                $vorhanden->each(function (Page $alt) {
                    $alt->blocks()->delete();
                    $alt->delete();
                });

                $this->uebersetzung($deutsch, $locale);
                $angelegt++;
            }
        }

        $this->glossar();
        $this->felder(\App\Models\Group::class, 'slug', 'uebersetzungen-gruppen.json');
        $this->felder(\App\Models\TeamMember::class, 'name', 'uebersetzungen-team.json');

        // Erst jetzt freischalten: Vorher hätte der Umschalter auf leere
        // Fassungen gezeigt. Nach dem Anlegen der Kernseiten ist der Rückfall
        // für den Rest ein regulärer, sichtbarer Zustand.
        Language::where('code', 'en')->update(['aktiv' => true]);
        Language::memoLeeren();

        $this->command?->info("{$angelegt} Übersetzungen angelegt, Englisch freigeschaltet.");
        $this->command?->warn('Hinweis: maschinelle Übersetzungen — vor dem Livegang vom Verein prüfen lassen.');
    }

    private function uebersetzung(Page $deutsch, string $locale): void
    {
        $seite = Page::create([
            'locale' => $locale,
            // Dieselbe Übersetzungsgruppe: darüber finden Umschalter, hreflang
            // und Menü die Fassungen zusammen.
            'uebersetzungs_gruppe' => $deutsch->uebersetzungs_gruppe,
            // Gleicher Slug, nur mit Sprachpräfix (/en/verein). Für die Demo
            // reicht das und kann sich mit keiner deutschen Adresse beissen —
            // eindeutig ist der Slug je Sprache.
            'slug' => $deutsch->slug,
            'titel' => $this->tr($deutsch->titel, $locale),
            // Das Titelbild zeigt keinen Text, es gilt für jede Sprache.
            'titelbild' => $deutsch->titelbild,
            'titelbild_alt' => $this->tr($deutsch->titelbild_alt, $locale),
            'untertitel' => $this->tr($deutsch->untertitel, $locale),
            'meta_title' => $this->tr($deutsch->meta_title, $locale),
            'meta_description' => $this->tr($deutsch->meta_description, $locale),
            // Maschinell übersetzt: sichtbar als ungeprüft gekennzeichnet und
            // nicht in Suchmaschinen, bis der Verein gegengelesen hat.
            'ungeprueft' => true,
            'noindex' => true,
            'published_at' => now(),
        ]);

        foreach ($deutsch->blocks as $block) {
            $seite->blocks()->create([
                'typ' => $block->typ,
                'position' => $block->position,
                'data' => $this->uebersetzeData($block->data ?? [], $locale),
            ]);
        }
    }

    /**
     * Läuft die Bausteindaten durch und übersetzt nur die Textfelder.
     *
     * @param  array<string|int, mixed>  $data
     * @return array<string|int, mixed>
     */
    private function uebersetzeData(array $data, string $locale): array
    {
        $ergebnis = [];

        foreach ($data as $schluessel => $wert) {
            if ($schluessel === 'absaetze' && is_array($wert)) {
                // Liste von Textabsätzen.
                $ergebnis[$schluessel] = array_map(
                    fn ($absatz) => is_string($absatz) ? $this->links($this->tr($absatz, $locale), $locale) : $absatz,
                    $wert,
                );
            } elseif (is_array($wert)) {
                // Verschachtelt: Karten, Knöpfe, Bank … je Eintrag weiterreichen.
                $ergebnis[$schluessel] = array_is_list($wert)
                    ? array_map(
                        fn ($eintrag) => is_array($eintrag) ? $this->uebersetzeData($eintrag, $locale) : $eintrag,
                        $wert,
                    )
                    : $this->uebersetzeData($wert, $locale);
            } elseif (is_string($wert) && in_array($schluessel, self::TEXT_FELDER, true)) {
                $ergebnis[$schluessel] = $this->links($this->tr($wert, $locale), $locale);
            } elseif (is_string($wert) && in_array($schluessel, ['url', 'alleUrl'], true)) {
                // Eigene Seiten in derselben Sprache: Seit 08.10.2026 gibt es
                // jede Seite auch auf Englisch.
                $ergebnis[$schluessel] = $this->pfad($wert, $locale);
            } else {
                // Links, Icons, Schalter, Zahlen: unverändert.
                $ergebnis[$schluessel] = $wert;
            }
        }

        return $ergebnis;
    }

    /**
     * Ein einzelner Text. Kennt das Wörterbuch den deutschen Satz nicht, bleibt
     * er deutsch stehen — sichtbar unübersetzt ist ehrlicher als falsch.
     */
    private function tr(?string $deutsch, string $locale): ?string
    {
        if ($deutsch === null || $deutsch === '') {
            return $deutsch;
        }

        return $this->woerterbuch[trim($deutsch)][$locale] ?? $deutsch;
    }

    /** Englische Glossarbegriffe, wo es noch keine gibt. */
    private function glossar(): void
    {
        $daten = $this->datei('uebersetzungen-glossar.json');

        foreach (\App\Models\GlossaryTerm::where('locale', Language::standardCode())->get() as $begriff) {
            $en = $daten[$begriff->slug] ?? null;

            if (! $en || \App\Models\GlossaryTerm::where('locale', 'en')
                ->where('uebersetzungs_gruppe', $begriff->uebersetzungs_gruppe)->exists()) {
                continue;
            }

            \App\Models\GlossaryTerm::create([
                'locale' => 'en',
                'uebersetzungs_gruppe' => $begriff->uebersetzungs_gruppe,
                'slug' => $begriff->slug,
                'kuerzel' => $en['kuerzel'] ?? $begriff->kuerzel,
                'begriff' => $en['begriff'],
                'erklaerung' => $en['erklaerung'],
                'mehr_url' => $begriff->mehr_url,
                'mehr_label' => $en['mehr_label'] ?? $begriff->mehr_label,
                'published_at' => $begriff->published_at,
            ]);
        }
    }

    /**
     * Sprachfassungen für Modelle mit Spalte `uebersetzungen` (Uebersetzbar),
     * nur wo für Englisch noch nichts steht.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $klasse
     */
    private function felder(string $klasse, string $schluessel, string $datei): void
    {
        $daten = $this->datei($datei);

        foreach ($klasse::all() as $eintrag) {
            $en = $daten[$eintrag->getRawOriginal($schluessel)] ?? null;
            $bisher = $eintrag->uebersetzungen ?? [];

            if (! $en || ! empty($bisher['en'])) {
                continue;
            }

            $eintrag->update(['uebersetzungen' => $bisher + ['en' => $en]]);
        }
    }

    /** @return array<string, mixed> */
    private function datei(string $name): array
    {
        $pfad = base_path('database/seeders/data/'.$name);

        return is_file($pfad) ? (json_decode(file_get_contents($pfad), true) ?? []) : [];
    }

    /** Eigener Pfad mit Sprachpräfix; fremde Adressen und Dateien unverändert. */
    private function pfad(string $url, string $locale): string
    {
        if (! str_starts_with($url, '/') || str_starts_with($url, '//') || preg_match(self::DATEIPFADE, $url)
            || preg_match('#^/'.preg_quote($locale, '#').'(/|$|\?|\#)#', $url)) {
            return $url;
        }

        return '/'.$locale.($url === '/' ? '' : $url);
    }

    /** [Text](/pfad) im Fliesstext auf die Sprachfassung zeigen lassen. */
    private function links(string $text, string $locale): string
    {
        return preg_replace_callback('/\]\((\/[^\s()]*)\)/u', fn ($m) => ']('.$this->pfad($m[1], $locale).')', $text);
    }
}
