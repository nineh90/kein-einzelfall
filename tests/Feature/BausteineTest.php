<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\TeamMember;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BausteineTest extends TestCase
{
    use RefreshDatabase;

    private function seiteMitBaustein(string $typ, array $daten): string
    {
        $seite = Page::create([
            'slug' => 'testseite',
            'titel' => 'Testseite',
            'published_at' => now(),
        ]);

        $seite->blocks()->create(['typ' => $typ, 'position' => 0, 'data' => $daten]);

        return $this->get('/testseite')->getContent();
    }

    public function test_zu_jedem_blocktyp_gibt_es_eine_komponente(): void
    {
        // Ein Typ ohne Komponente wird beim Rendern still übersprungen — die
        // Redaktion könnte ihn im Panel auswählen und würde nichts sehen.
        foreach (array_keys(PageBlock::TYPEN) as $typ) {
            $this->assertTrue(
                view()->exists('components.blocks.'.str_replace('_', '-', $typ)),
                "Blocktyp '{$typ}' hat keine Komponente"
            );
        }
    }

    public function test_ablauf_in_schritten_ist_eine_nummerierte_liste(): void
    {
        // Als <ol> ausgezeichnet, damit Screenreader „1 von 3" ansagen.
        $html = $this->seiteMitBaustein('schritte', [
            'titel' => 'So stellst du den Antrag',
            'schritte' => [
                ['titel' => 'Formular anfordern', 'text' => 'Beim Versorgungsamt.'],
                ['titel' => 'Unterlagen sammeln', 'text' => 'Atteste und Nachweise.'],
                ['titel' => 'Antrag abgeben', 'text' => 'Schriftlich einreichen.'],
            ],
        ]);

        $this->assertStringContainsString('<ol', $html);
        $this->assertStringContainsString('Formular anfordern', $html);
        $this->assertStringContainsString('Antrag abgeben', $html);
    }

    public function test_fragen_und_antworten_funktionieren_ohne_javascript(): void
    {
        $html = $this->seiteMitBaustein('accordion', [
            'titel' => 'Häufige Fragen',
            'eintraege' => [
                ['frage' => 'Wie lange dauert das?', 'antwort' => '<p>Unterschiedlich.</p>'],
            ],
        ]);

        // Natives <details> statt Alpine
        $this->assertStringContainsString('<details', $html);
        $this->assertStringContainsString('Wie lange dauert das?', $html);
        // Antwort bleibt im Dokument — auch für Suchmaschinen sichtbar
        $this->assertStringContainsString('Unterschiedlich.', $html);
    }

    public function test_fragen_und_antworten_bringen_faq_auszeichnung_mit(): void
    {
        $html = $this->seiteMitBaustein('accordion', [
            'eintraege' => [['frage' => 'Was kostet das?', 'antwort' => '<p>Nichts.</p>']],
        ]);

        $this->assertStringContainsString('"@type":"FAQPage"', $html);
        $this->assertStringContainsString('"@type":"Question"', $html);
    }

    public function test_aufmacher_unterlegt_den_markierten_teil_mit_der_linie(): void
    {
        // Die handgezeichnete Linie aus dem Mockup. Der Verein markiert den
        // Teil, der sie bekommt, mit Sternchen.
        $html = $this->seiteMitBaustein('hero', [
            'titel' => 'Keiner soll mehr sagen müssen: *Ich hab es nicht gewusst!*',
        ]);

        $this->assertStringContainsString('<span class="swash">', $html);

        // Der Text steht vollständig da — und die Sternchen sind weg, nicht
        // etwa mitgelesen.
        $this->assertStringContainsString('Ich hab es nicht gewusst!', $html);
        $this->assertStringNotContainsString('*Ich hab', $html);
    }

    public function test_aufmacher_ohne_markierung_bleibt_ohne_linie(): void
    {
        $html = $this->seiteMitBaustein('hero', ['titel' => 'Ganz ohne Linie']);

        $this->assertStringContainsString('Ganz ohne Linie', $html);
        $this->assertStringNotContainsString('swash', $html);
    }

    public function test_markierung_im_titel_kann_kein_html_einschleusen(): void
    {
        // Der Titel kommt aus dem Panel. Die Auszeichnung ist der einzige Weg,
        // aus einem Titel Markup zu machen — alles andere bleibt Text.
        $html = $this->seiteMitBaustein('hero', [
            'titel' => 'Harmlos *<script>alert(1)</script>*',
        ]);

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_leere_knoepfe_und_karten_landen_nicht_auf_der_seite(): void
    {
        // Im Panel entstehen sie mit einem Klick: Eintrag hinzufügen, Felder
        // leer lassen. Ein <a href=""> wäre ein Link ohne Namen und Ziel —
        // ein Stolperstopp für die Tastatur und ein WCAG-Verstoss.
        $html = $this->seiteMitBaustein('hero', [
            'titel' => 'Mit einem halben Knopf',
            'ctas' => [
                ['label' => 'Echter Knopf', 'url' => '/spenden'],
                ['label' => 'Ohne Ziel', 'url' => null],
                ['label' => null, 'url' => '/verein'],
            ],
        ]);

        $this->assertStringContainsString('Echter Knopf', $html);
        $this->assertStringNotContainsString('Ohne Ziel', $html);
        $this->assertStringNotContainsString('href=""', $html);
    }

    public function test_inhaltsverzeichnis_nennt_nur_bausteine_mit_sprungziel(): void
    {
        // Nur Textbausteine setzen ein id-Attribut. Stünde ein anderer Baustein
        // im Verzeichnis, führte sein Eintrag ins Nichts.
        $seite = Page::create(['slug' => 'lang', 'titel' => 'Lang', 'published_at' => now()]);

        foreach (['Eins', 'Zwei', 'Drei', 'Vier'] as $i => $titel) {
            $seite->blocks()->create([
                'typ' => 'text',
                'position' => $i,
                'data' => ['titel' => $titel, 'absaetze' => ['Text.']],
            ]);
        }

        $seite->blocks()->create([
            'typ' => 'quick_access',
            'position' => 4,
            'data' => ['titel' => 'Einstiege ohne Sprungziel', 'karten' => []],
        ]);

        $html = $this->get('/lang')->getContent();

        $this->assertStringContainsString('#abschnitt-eins', $html);
        $this->assertStringNotContainsString('#abschnitt-einstiege-ohne-sprungziel', $html);
    }

    public function test_hinweis_kennt_verschiedene_dringlichkeiten(): void
    {
        $frist = $this->seiteMitBaustein('hinweis', [
            'art' => 'frist',
            'text' => 'Der Widerspruch muss innerhalb eines Monats eingehen.',
        ]);

        $this->assertStringContainsString('Auf die Frist achten', $frist);
        $this->assertStringContainsString('innerhalb eines Monats', $frist);
    }

    public function test_text_mit_bild_zeigt_platzhalter_solange_kein_bild_da_ist(): void
    {
        // Bildmaterial kommt vom Verein — bis dahin soll die Seite trotzdem
        // fertig aussehen.
        $html = $this->seiteMitBaustein('text_media', [
            'titel' => 'Wer wir sind',
            'absaetze' => ['Ein Absatz.'],
        ]);

        $this->assertStringContainsString('Platzhalter', $html);
        $this->assertStringContainsString('Wer wir sind', $html);
    }

    public function test_text_mit_bild_setzt_alt_text_wenn_vorhanden(): void
    {
        $html = $this->seiteMitBaustein('text_media', [
            'absaetze' => ['Text.'],
            'bild' => '/img/logo.png',
            'bild_alt' => 'Vorstand des Vereins bei einer Sitzung',
        ]);

        $this->assertStringContainsString('alt="Vorstand des Vereins bei einer Sitzung"', $html);
    }

    public function test_dokumentenliste_bleibt_im_satzspiegel(): void
    {
        // Vorher lief sie randlos über die volle Fensterbreite und fiel aus
        // dem Layout der Seite.
        $html = $this->seiteMitBaustein('download_list', [
            'titel' => 'Dokumente',
            'dokumente' => [['titel' => 'Merkblatt', 'url' => '/test.pdf', 'bytes' => 102400]],
        ]);

        preg_match('/<section[^>]*aria-labelledby="dl-[^"]*"[^>]*>(.*?)<\/section>/s', $html, $m);
        $abschnitt = $m[0] ?? '';

        $this->assertStringContainsString('max-w-6xl', $abschnitt);
        $this->assertStringContainsString('PDF-Datei, 100 KB', $abschnitt);
    }

    public function test_dokumentenliste_kennzeichnet_verweise_auf_behoerdenseiten(): void
    {
        /*
         * Entscheidung aus der Besprechung vom 02.08.2026: Antragsformulare
         * werden nicht mehr selbst gehostet, sondern bei der Behörde verlinkt —
         * Ämter ändern ihre Vordrucke, und eine veraltete Kopie kostet die
         * Antragstellerin Zeit, die sie oft nicht hat.
         *
         * Ein Verweis nach draußen muss aber vor dem Antippen als solcher zu
         * erkennen sein (WCAG 3.2.5).
         */
        $html = $this->seiteMitBaustein('download_list', [
            'titel' => 'Anträge',
            'dokumente' => [
                ['titel' => 'Antrag auf Erwerbsminderungsrente', 'url' => 'https://www.deutsche-rentenversicherung.de/formular', 'quelle' => 'Deutsche Rentenversicherung'],
                ['titel' => 'Merkblatt des Vereins', 'url' => '/test.pdf', 'bytes' => 102400],
            ],
        ]);

        $this->assertStringContainsString('Öffnet Deutsche Rentenversicherung', $html);
        $this->assertStringContainsString('Anträge und Formulare verlinken wir', $html);

        // Die eigene Datei bleibt ein Download und behält ihre Größenangabe.
        $this->assertStringContainsString('PDF-Datei, 100 KB', $html);
    }

    public function test_dokumentenliste_nennt_ohne_gepflegten_namen_die_adresse(): void
    {
        $html = $this->seiteMitBaustein('download_list', [
            'dokumente' => [['titel' => 'Antrag', 'url' => 'https://www.arbeitsagentur.de/formular']],
        ]);

        // „www.“ fällt weg: Es sagt niemandem etwas und macht die Zeile länger.
        $this->assertStringContainsString('Öffnet arbeitsagentur.de', $html);
    }

    public function test_dokumentenliste_bietet_fremde_ziele_nicht_als_download_an(): void
    {
        $html = $this->seiteMitBaustein('download_list', [
            'dokumente' => [['titel' => 'Antrag', 'url' => 'https://www.arbeitsagentur.de/formular']],
        ]);

        preg_match('/<a href="https:\/\/www\.arbeitsagentur\.de\/formular"[^>]*>/', $html, $m);
        $link = $m[0] ?? '';

        $this->assertNotSame('', $link);

        // download an einem fremden Ziel tut nichts und verspricht trotzdem
        // etwas — der Browser ignoriert es bei fremder Herkunft schlicht.
        $this->assertStringNotContainsString('download', $link);
        $this->assertStringContainsString('noreferrer', $link);
    }

    public function test_spendenblock_bringt_einen_qr_code_fuer_die_ueberweisung_mit(): void
    {
        /*
         * Wunsch aus der Besprechung vom 02.08.2026. 22 Stellen IBAN abzutippen
         * ist fehleranfällig — und für Menschen mit Konzentrations- oder
         * Sehschwierigkeiten eine echte Hürde.
         */
        $html = $this->seiteMitBaustein('donation_options', [
            'bank' => [
                'institut' => 'Deutsche Skatbank',
                'iban' => 'DE79 8306 5408 0006 8893 10',
                'bic' => 'GENODEF1SLR',
            ],
        ]);

        // Der Code steht als SVG im Dokument. Kein <img src="https://…">:
        // Ein fremd geladener QR-Code verriete dem Anbieter, wer spenden will.
        $this->assertStringContainsString('svg', $html);
        $this->assertStringContainsString('girocode', $html);
        $this->assertStringNotContainsString('api.qrserver.com', $html);

        // Die Angaben zum Abtippen bleiben — ohne Kamera, ohne App, ohne
        // Smartphone muss man genauso weit kommen.
        $this->assertStringContainsString('DE79 8306 5408 0006 8893 10', $html);
    }

    public function test_iban_kopieren_erscheint_erst_mit_javascript(): void
    {
        // Ohne Skript bliebe ein Knopf, der nichts tut. Kopiert wird ohne
        // Leerzeichen — das nehmen alle Überweisungsformulare an.
        $html = $this->seiteMitBaustein('donation_options', [
            'bank' => ['iban' => 'DE79 8306 5408 0006 8893 10'],
        ]);

        $this->assertMatchesRegularExpression('/data-kopieren-bereich hidden/', $html);
        $this->assertStringContainsString('data-kopieren="DE79830654080006889310"', $html);
        $this->assertMatchesRegularExpression('/role="status"[^>]*data-kopieren-status/', $html);
    }

    public function test_unvollstaendige_bankverbindung_erzeugt_keinen_qr_code(): void
    {
        // Ein Code auf eine halbe IBAN führte eine Spende ins Leere. Lieber
        // keiner — die Angaben daneben stehen ja weiterhin da.
        $html = $this->seiteMitBaustein('donation_options', [
            'bank' => ['institut' => 'Deutsche Skatbank', 'iban' => 'DE79 8306'],
        ]);

        $this->assertStringNotContainsString('girocode', $html);
        $this->assertStringContainsString('Deutsche Skatbank', $html);
    }

    public function test_der_qr_code_sagt_vorlesehilfen_was_er_ist(): void
    {
        // Ein QR-Code ist für eine Vorlesehilfe eine Fläche und sonst nichts.
        $html = $this->seiteMitBaustein('donation_options', [
            'bank' => ['iban' => 'DE79830654080006889310'],
        ]);

        $this->assertStringContainsString('role="img"', $html);
        $this->assertStringContainsString('QR-Code mit der Bankverbindung', $html);
    }

    public function test_partner_stehen_auch_ohne_logo_da(): void
    {
        // Der Verein soll Kooperationen eintragen können, bevor er von jedem
        // eine Bilddatei hat. Sonst bleibt der Bereich monatelang leer.
        $html = $this->seiteMitBaustein('partner_logos', [
            'titel' => 'Kooperationen',
            'partner' => [
                ['name' => 'Aktion Mensch', 'url' => 'https://www.aktion-mensch.de'],
                ['name' => 'Der Paritätische', 'rolle' => 'Dachverband'],
                ['name' => ''],   // leerer Eintrag, fliegt raus
            ],
        ]);

        $this->assertStringContainsString('Aktion Mensch', $html);
        $this->assertStringContainsString('Der Paritätische', $html);
        $this->assertStringContainsString('Dachverband', $html);
    }

    public function test_partner_ohne_ziel_wird_kein_link(): void
    {
        // Ein <a> ohne href ist für die Tastatur nicht erreichbar und
        // verspricht trotzdem einen Klick.
        $html = $this->seiteMitBaustein('partner_logos', [
            'partner' => [['name' => 'ANUAS e.V.']],
        ]);

        $this->assertStringContainsString('ANUAS e.V.', $html);
        $this->assertDoesNotMatchRegularExpression('/<a(?![^>]*href)[^>]*>\s*ANUAS/s', $html);
    }

    public function test_partnerlogo_ist_dekorativ_und_wiederholt_den_namen_nicht(): void
    {
        /*
         * Vorgelesen würde ein Logo mit alt="Aktion Mensch Logo" neben dem
         * Namen zu „Aktion Mensch Logo Link Aktion Mensch". Die Doppelung
         * stört genau die Menschen, für die der Alternativtext gedacht ist.
         */
        $html = $this->seiteMitBaustein('partner_logos', [
            'partner' => [['name' => 'Aktion Mensch', 'logo' => '/img/partner/aktion-mensch.svg']],
        ]);

        preg_match('/<img[^>]*aktion-mensch[^>]*>/', $html, $m);

        $this->assertNotEmpty($m, 'Das Logo fehlt');
        $this->assertStringContainsString('alt=""', $m[0]);
    }

    public function test_themenliste_zeigt_eintraege_und_laesst_leere_aus(): void
    {
        // Der Feldname im Panel ist „alleUrl“ (camelCase). Dieser Test hält fest,
        // dass genau dieser Schlüssel bei der Komponente ankommt — kebab-case
        // käme still nicht an, und der Verweis fehlte ohne Fehlermeldung.
        $html = $this->seiteMitBaustein('topic_list', [
            'titel' => 'Wissen',
            'alleUrl' => '/wissen',
            'alleLabel' => 'Zum Wissensbereich',
            'themen' => [
                ['label' => 'Erwerbsminderungsrente', 'url' => '/erwerbsminderungsrente', 'icon' => 'shield'],
                ['label' => '', 'url' => ''],   // halb leer → fällt raus
            ],
        ]);

        $this->assertStringContainsString('Erwerbsminderungsrente', $html);
        $this->assertStringContainsString('/wissen', $html);
        $this->assertStringContainsString('Zum Wissensbereich', $html);
        $this->assertStringNotContainsString('href=""', $html);
    }

    public function test_kennzahlen_erscheinen_als_wert_und_bezeichnung(): void
    {
        $html = $this->seiteMitBaustein('stat_strip', [
            'stats' => [
                ['wert' => '2024', 'label' => 'gegründet'],
                ['wert' => '1.000+', 'label' => 'erreichte Menschen'],
            ],
        ]);

        $this->assertStringContainsString('2024', $html);
        $this->assertStringContainsString('erreichte Menschen', $html);
    }

    public function test_inhaltshinweis_nennt_das_thema(): void
    {
        $html = $this->seiteMitBaustein('inhalts_hinweis', ['thema' => 'Schilderung von Gewalt']);

        $this->assertStringContainsString('Hinweis zum Inhalt: Schilderung von Gewalt', $html);
        // Natives <details>, ohne JavaScript bedienbar.
        $this->assertStringContainsString('<details', $html);
    }

    public function test_eingebetteter_inhalt_laedt_erst_nach_zustimmung(): void
    {
        $html = $this->seiteMitBaustein('embed', [
            'titel' => 'Unser Spendenprojekt',
            'anbieter' => 'betterplace.org',
            'src' => 'https://project-widget.betterplace.org/de/projects/12345/widget',
        ]);

        // Der Anbieter wird vorher genannt …
        $this->assertStringContainsString('betterplace.org', $html);

        // … aber der Rahmen steckt im <template>, das der Browser nicht lädt.
        // Ausserhalb davon darf keine Anbieter-Adresse stehen — dasselbe Muster
        // wie im DatenschutzTest.
        $ohneTemplate = preg_replace('/<template[^>]*>.*?<\/template>/s', '', $html);
        $this->assertStringNotContainsString('<iframe', $ohneTemplate);
        $this->assertStringNotContainsString('project-widget.betterplace.org', $ohneTemplate);
    }

    public function test_spendenmoeglichkeiten_zeigen_die_angaben_des_vereins(): void
    {
        $html = $this->seiteMitBaustein('donation_options', [
            'titel' => 'Jetzt spenden',
            'bank' => ['institut' => 'GLS Bank', 'iban' => 'DE00 0000 0000 0000 0000 00', 'bic' => 'GENODEM1GLS'],
            'bescheinigung' => ['email' => 'spenden@kein-einzelfall.de'],
        ]);

        $this->assertStringContainsString('GLS Bank', $html);
        $this->assertStringContainsString('spenden@kein-einzelfall.de', $html);
        // Nur die E-Mail gepflegt, kein Hinweistext — darf keine Fehlermeldung geben.
        $this->assertStringContainsString('Spendenbescheinigung', $html);
    }

    public function test_vorstand_und_gruppen_ziehen_aus_der_verwaltung(): void
    {
        // Diese beiden Bausteine tragen keinen eigenen Inhalt — sie zeigen, was
        // unter „Vorstand & Team“ und „Gruppen“ gepflegt ist. Im Panel wird nur
        // gewählt, welcher Ausschnitt.
        TeamMember::create([
            'name' => 'Alex Beispiel', 'bereich' => 'Vorstand',
            'kurzprofil' => 'Gründungsmitglied.', 'published_at' => now(),
        ]);
        Group::create([
            'slug' => 'montagsgruppe', 'name' => 'Montagsgruppe', 'typ' => 'selbsthilfe',
            'teaser' => 'Offener Austausch.', 'status' => 'offen', 'published_at' => now(),
        ]);

        // Beide Bausteine auf einer Seite — der Helfer legt sonst zweimal
        // denselben Slug an.
        $seite = Page::create(['slug' => 'uebersicht', 'titel' => 'Übersicht', 'published_at' => now()]);
        $seite->blocks()->create(['typ' => 'team_grid', 'position' => 0, 'data' => ['bereich' => 'Vorstand']]);
        $seite->blocks()->create(['typ' => 'group_list', 'position' => 1, 'data' => ['typ' => 'selbsthilfe']]);

        $html = $this->get('/uebersicht')->getContent();

        $this->assertStringContainsString('Alex Beispiel', $html);
        $this->assertStringContainsString('Montagsgruppe', $html);
    }

    public function test_seitentitel_verwenden_umlaute(): void
    {
        // Aus dem Slug abgeleitet hiesse die Seite „Ueber Uns Vorstand Und Team".
        $this->seed(AltseiteSeeder::class);

        foreach (Page::pluck('titel', 'slug') as $slug => $titel) {
            $this->assertDoesNotMatchRegularExpression(
                '/\b\w*(ae|oe|ue|Ae|Oe|Ue)\w+\b/u',
                $titel,
                "Titel von /{$slug} schreibt Umlaute aus: „{$titel}“"
            );
        }

        $this->assertSame(
            'Über uns – Vorstand und Team',
            Page::where('slug', 'ueber-uns-vorstand-und-team')->value('titel')
        );
    }
}
