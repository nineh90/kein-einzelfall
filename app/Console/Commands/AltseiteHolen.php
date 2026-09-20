<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Holt den Inhalt der WordPress-Altseite und legt ihn als JSON ab.
 *
 * Bewusst ein eigener Schritt, getrennt vom Einpflegen: Der Abzug ist
 * reproduzierbar und überprüfbar, bevor irgendetwas in die Datenbank geht.
 * Wenn der Verein zwischendurch Texte ändert, holt man ihn einfach neu und
 * sieht am git-Diff, was sich geändert hat.
 *
 *   php artisan altseite:holen
 *   php artisan altseite:holen --seite=/verein/
 */
class AltseiteHolen extends Command
{
    protected $signature = 'altseite:holen
                            {--seite= : Nur diese eine URL holen}
                            {--ziel=docs/altseite-inhalt.json : Zieldatei}';

    protected $description = 'Inhalt der WordPress-Altseite als JSON sichern';

    private const BASIS = 'https://kein-einzelfall.de';

    /** Die 24 Seiten aus der sitemap.xml (Stand 25.07.2026). */
    private const SEITEN = [
        '/', '/verein/', '/ueber-uns-vorstand-und-team/', '/satzung/', '/mitgliedschaft/',
        '/selbsthilfegruppen/', '/arbeitsgruppen/', '/veranstaltungen/', '/anfragen/',
        '/kontakt/', '/spenden/', '/das-hilfesystem/', '/unterstuetzung/',
        '/fsm-erweitertes-hilfesystem/', '/buerokratie-labyrinth/', '/kein-einzelfall-im-dialog/',
        '/trauma-bindung-und-beziehung/', '/traumafolgestoerungen-verstehen/',
        '/erwerbsminderungsrente/', '/istanbul-konvention/', '/wissen/', '/kinderkodex/',
        '/datenschutz/', '/impressum-2/',
    ];

    public function handle(): int
    {
        $seiten = $this->option('seite') ? [$this->option('seite')] : self::SEITEN;
        $ergebnis = [];

        $balken = $this->output->createProgressBar(count($seiten));
        $balken->start();

        foreach ($seiten as $pfad) {
            try {
                $html = Http::withHeaders(['User-Agent' => 'NilsDigital-Migration/1.0'])
                    ->timeout(30)->get(self::BASIS.$pfad)->body();

                $ergebnis[$pfad] = $this->seiteAuslesen($html, $pfad);
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("  {$pfad}: {$e->getMessage()}");
            }
            $balken->advance();
            usleep(300_000);   // die Seite liegt auf Shared Hosting — nicht hämmern
        }

        $balken->finish();
        $this->newLine(2);

        $ziel = base_path($this->option('ziel'));
        file_put_contents($ziel, json_encode($ergebnis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $this->info(count($ergebnis).' Seiten gesichert → '.$this->option('ziel'));

        foreach ($ergebnis as $pfad => $seite) {
            $this->line(sprintf(
                '  %-38s %2d Blöcke  %4d Wörter%s',
                $pfad,
                count($seite['bloecke']),
                $seite['woerter'],
                ($seite['pdfs'] ? '  '.count($seite['pdfs']).' PDFs' : '')
                    .(($bilder = count(array_filter($seite['bloecke'], fn ($b) => isset($b['bild']))))
                        ? "  {$bilder} Bilder" : '')
            ));
        }

        return self::SUCCESS;
    }

    /** Metadaten und Inhaltsblöcke einer Seite herauslösen. */
    private function seiteAuslesen(string $html, string $pfad): array
    {
        $meta = [
            'meta_title' => $this->treffer('/<title[^>]*>(.*?)<\/title>/s', $html),
            'meta_description' => $this->treffer('/<meta name="description" content="([^"]*)"/i', $html),
        ];

        $inhalt = $this->inhaltsbereich($html);

        return array_merge($meta, [
            'titel' => $this->treffer('/<h1[^>]*>(.*?)<\/h1>/s', $inhalt),
            'bloecke' => $this->bloecke($inhalt),
            'pdfs' => $this->pdfs($inhalt),
            'woerter' => str_word_count(strip_tags($inhalt)),
        ]);
    }

    /**
     * Den eigentlichen Seiteninhalt herausschneiden.
     *
     * Elementor markiert ihn mit data-elementor-type="wp-page", das Theme schließt
     * mit <!-- #page -->. Alles danach ist Footer, Cookie-Banner und das
     * OneTap-Widget — letzteres allein bringt 42 Sprachlisten samt Flaggenbildern
     * mit und würde jede Auswertung unbrauchbar machen.
     */
    private function inhaltsbereich(string $html): string
    {
        $start = strpos($html, 'data-elementor-type="wp-page"');
        if ($start === false) {
            $start = 0;
        }

        $inhalt = substr($html, $start);

        foreach (['<!-- #page -->', 'onetap', 'Werkzeugleiste'] as $ende) {
            $pos = strpos($inhalt, $ende);
            if ($pos !== false) {
                $inhalt = substr($inhalt, 0, $pos);
                break;
            }
        }

        return preg_replace('/<(script|style|noscript)\b.*?<\/\1>/s', '', $inhalt);
    }

    /**
     * Überschriften und Absätze in Blöcke gruppieren.
     *
     * Eine Überschrift beginnt einen neuen Block, die folgenden Absätze gehören
     * dazu. Das entspricht der Struktur, die wir mit `page_blocks` abbilden.
     */
    private function bloecke(string $html): array
    {
        // \b hinter dem Tagnamen: Ohne die Wortgrenze passte das Muster auch
        // auf <path …> der SVG-Icons und las von dort bis zum nächsten </p> —
        // samt allem dazwischen. Auf der Teamseite steckt so ein Icon im
        // Kontaktknopf jeder Person, und jede zweite Person verschwand im
        // Bauch der vorigen: Vier von sieben fehlten im Abzug vom Juli, ohne
        // dass es jemandem auffiel.
        //
        // Der Inhalt darf ausserdem kein weiteres <p>, <li> oder <h…>
        // enthalten — sonst überbrückte ein einzelnes ungeschlossenes <p> die
        // nächsten Abschnitte genauso.
        //
        // <li> zählt als Absatz: Die Kurzangaben einer Person stehen auf der
        // Altseite als Aufzählung und fielen sonst weg. Bilder gehören zum
        // Block, in dem sie stehen — auf der Teamseite ist das der Name.
        preg_match_all(
            '/<(h[1-6]|p|li)\b[^>]*>((?:(?!<(?:h[1-6]|p|li)\b).)*?)<\/\1>|<(img)\b([^>]*)>/s',
            $html, $treffer, PREG_SET_ORDER
        );

        $bloecke = [];
        $aktuell = null;

        foreach ($treffer as $t) {
            if (($t[3] ?? '') === 'img') {
                $bild = $this->bild($t[4]);

                if ($bild && $aktuell && ! isset($aktuell['bild'])) {
                    $aktuell['bild'] = $bild;
                }

                continue;
            }

            [$_, $tag, $roh] = $t;
            $text = $this->text($roh);
            if ($text === '' || mb_strlen($text) < 3) {
                continue;
            }

            if ($tag === 'h1') {
                continue;   // ist der Seitentitel, kein Inhaltsblock
            }

            if (str_starts_with($tag, 'h')) {
                if ($aktuell) {
                    $bloecke[] = $aktuell;
                }
                $aktuell = ['typ' => 'text', 'titel' => $text, 'absaetze' => []];

                continue;
            }

            if (! $aktuell) {
                $aktuell = ['typ' => 'text', 'titel' => null, 'absaetze' => []];
            }

            // Bewusst nicht zusammengefügt, wenn ein <p> mitten im Satz endet:
            // Die Altseite setzt Adressen, Linklisten und Zwischenzeilen als
            // einzelne <p>, und eine solche Regel klebte sie auf zwölf Seiten
            // zusammen. Wo es wirklich ein Umbruch ist, richtet es der Seeder.
            $aktuell['absaetze'][] = $text;
        }

        if ($aktuell) {
            $bloecke[] = $aktuell;
        }

        // Blöcke ohne jeden Fließtext sind meist Layout-Reste
        return array_values(array_filter(
            $bloecke,
            fn ($b) => $b['absaetze'] !== [] || $b['titel'] !== null
        ));
    }

    /** Verlinkte Dokumente samt Linktext — der ist als Titel viel besser als der Dateiname. */
    private function pdfs(string $html): array
    {
        preg_match_all('/<a[^>]+href="([^"]+\.pdf)"[^>]*>(.*?)<\/a>/is', $html, $treffer, PREG_SET_ORDER);

        $pdfs = [];
        foreach ($treffer as [$_, $url, $roh]) {
            $pdfs[] = [
                'url' => str_replace(self::BASIS, '', $url),
                'titel' => $this->text($roh),
            ];
        }

        return $pdfs;
    }

    /**
     * Quelle und Beschreibung eines Bildes im Inhalt — oder null, wenn es
     * kein Inhaltsbild ist (Emoji-Grafiken, eingebettete Daten).
     *
     * Elementor liefert für Porträts nur eine Vorschaugröße unter
     * `elementor/thumbs/NAME-<hash>.jpg`, ohne Verweis auf das Original. Das
     * Original steht in der Mediathek; sie wird einmal über die
     * WordPress-Schnittstelle geholt und über den Dateinamen abgeglichen.
     *
     * @return array{src: string, alt: string}|null
     */
    private function bild(string $attribute): ?array
    {
        if (! preg_match('/\ssrc="([^"]+)"/', $attribute, $m)) {
            return null;
        }

        $src = html_entity_decode($m[1]);

        if (str_starts_with($src, 'data:') || str_contains($src, '/emoji/')) {
            return null;
        }

        $alt = preg_match('/\salt="([^"]*)"/', $attribute, $a) ? $this->text($a[1]) : '';

        return [
            'src' => str_replace(self::BASIS, '', $this->original($src)),
            'alt' => $alt,
        ];
    }

    /** @var array<string, string>|null Dateiname ohne Endung → Adresse des Originals */
    private ?array $mediathek = null;

    private function original(string $src): string
    {
        $datei = pathinfo(parse_url($src, PHP_URL_PATH) ?: '', PATHINFO_FILENAME);

        // Elementor-Vorschau: NAME-<hash>; WordPress-Größen: NAME-300x200
        $stamm = preg_replace(['/-[a-z0-9]{20,}$/', '/-\d+x\d+$/'], '', $datei);

        return $this->mediathek()[$stamm] ?? $src;
    }

    /** @return array<string, string> */
    private function mediathek(): array
    {
        if ($this->mediathek !== null) {
            return $this->mediathek;
        }

        $this->mediathek = [];

        for ($seite = 1; $seite <= 10; $seite++) {
            try {
                $antwort = Http::withHeaders(['User-Agent' => 'NilsDigital-Migration/1.0'])
                    ->timeout(30)
                    ->get(self::BASIS.'/wp-json/wp/v2/media', [
                        'media_type' => 'image', 'per_page' => 100, 'page' => $seite,
                    ]);
            } catch (\Throwable) {
                break;
            }

            $eintraege = $antwort->successful() ? $antwort->json() : [];

            if (! is_array($eintraege) || $eintraege === []) {
                break;
            }

            foreach ($eintraege as $medium) {
                $url = $medium['source_url'] ?? null;
                if ($url) {
                    $this->mediathek[pathinfo($url, PATHINFO_FILENAME)] = $url;
                }
            }

            if (count($eintraege) < 100) {
                break;
            }
        }

        return $this->mediathek;
    }

    private function text(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00a0}", ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    private function treffer(string $muster, string $html): ?string
    {
        return preg_match($muster, $html, $m) ? $this->text($m[1]) : null;
    }
}
