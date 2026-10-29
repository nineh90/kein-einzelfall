<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\AnfragenSeiteSeeder;
use Database\Seeders\KontaktSeiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Taddis Tickets zum Bereich Kontakt (KEV-266 bis KEV-277): Kontakt,
 * Landesstellen, Anfragen & Austausch und das Kontaktformular.
 */
class KontaktbereichTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    /** KEV-266 bis KEV-272 */
    public function test_kontaktseite_nach_taddis_tickets(): void
    {
        // Ab <main: Die Meta-Beschreibung im Kopf nennt den alten Titel noch.
        $html = strstr($this->get('/kontakt')->assertOk()->getContent(), '<main');

        // KEV-266: Taddis Bild (Notizbuch, Stift, Handy) unter demselben Namen.
        $this->assertSame([2000, 1126], array_slice(getimagesize(public_path('img/titelbilder/kontakt.webp')), 0, 2));

        // KEV-271, KEV-272: kurze Überschrift, Taddis Text statt der Anschrift.
        $this->assertMatchesRegularExpression('/<h2[^>]*>\s*Kontaktmöglichkeiten\s*<\/h2>/u', $html);
        $this->assertStringNotContainsString('Kontaktmöglichkeiten des', $html);
        $this->assertMatchesRegularExpression('/Bei <strong[^>]*>KE!N EINZELFALL e\.V\.<\/strong> möchten wir dir diesen Weg/u', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Du bist bei uns willkommen – mit allem, was dich bewegt\.<\/strong>/u', $html);
        // Die Anschrift steht nur noch unter „Kontakt & Geschäftssitz“.
        $this->assertStringNotContainsString('<p>Schiffbeker Höhe 30</p>', $html);
        $this->assertStringContainsString('<p>Anschrift: Schiffbeker Höhe 30, 22119 Hamburg</p>', $html);

        // KEV-267
        $this->assertStringContainsString('Kassenwartin: Petra Hildebrandt, <a href="mailto:finanzen@kein-einzelfall.de"', $html);
        $this->assertStringNotContainsString('hildebrandt@', $html);

        // KEV-270: Anfragen zuerst, Finanzen an dritter Stelle.
        $reihe = array_map(fn ($a) => strpos($html, $a), ['Anfragen: <a', 'Verwaltung: <a', 'Finanzen: <a', 'Selbsthilfegruppen: <a', 'Opferbeauftragter: <a']);
        $sortiert = $reihe;
        sort($sortiert);
        $this->assertSame($sortiert, $reihe);

        // KEV-268, KEV-269: In Listen hebt sich die erste Zeile nicht ab.
        preg_match('/Vorstandsebene.*?<div class="([^"]*)"/s', $html, $vorstand);
        $this->assertStringNotContainsString('first-child', $vorstand[1]);
        preg_match('/Kontaktmöglichkeiten\s*<\/h2>.*?<div class="([^"]*)"/s', $html, $einleitung);
        $this->assertStringContainsString('first-child', $einleitung[1]);
    }

    /** Was der Verein geändert hat, bleibt; zweimal laufen ändert nichts. */
    public function test_kontakt_tauscht_nur_den_alten_wortlaut(): void
    {
        $seite = Page::where('slug', 'kontakt')->where('locale', 'de')->sole();
        $sachgebiete = $seite->blocks()->get()->first(fn ($b) => ($b->data['titel'] ?? null) === 'Sachgebietsaufteilung');
        $sachgebiete->update(['data' => ['titel' => 'Sachgebietsaufteilung', 'absaetze' => ['Vom Verein.']]]);

        KontaktSeiteSeeder::aktualisieren();
        KontaktSeiteSeeder::aktualisieren();

        $this->assertSame(['Vom Verein.'], $sachgebiete->fresh()->data['absaetze']);
    }

    /** KEV-273: neue Unterzeile, der alte erste Satz steht wieder im Text. */
    public function test_landesstellen_unterzeile(): void
    {
        $html = $this->get('/landesstellen')->getContent();

        $this->assertMatchesRegularExpression('/<p class="mt-4[^"]*">Gemeinsam stark – an vielen Orten<\/p>/u', $html);
        $this->assertStringContainsString('<p>KE!N EINZELFALL ist nicht nur an einem Ort zuhause.</p>', $html);
    }

    /** KEV-274 bis KEV-276 */
    public function test_anfragen_und_austausch(): void
    {
        $html = $this->get('/anfragen')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Anfragen &amp; Austausch\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Was dich bewegt, bewegt auch uns.', $html);
        $this->assertStringContainsString('<title>Anfragen &amp; Austausch - Kein Einzelfall e.V.</title>', $html);

        $this->assertStringContainsString('bedeutet für uns auch, zuzuhören, Wissen zu teilen', $html);
        $this->assertStringContainsString('Jedes Anliegen ist uns wichtig, und wir nehmen uns die Zeit, die es verdient.', $html);
        $this->assertStringNotContainsString('48 Stunden', $html);

        // Die Freigabe, auf die beide Texte verweisen, unter dem Ablauf.
        $this->assertMatchesRegularExpression('#Wie funktioniert der Service\?.*?href="/dokumente/2026/10/Freigabe-Verarbeitung-und-Speicherung\.pdf"#s', $html);

        // Krisendienste mit den Nummern, vor dem Formular. An den Sprungzielen
        // gemessen: Seit KEV-275 stehen beide Namen auch im Verzeichnis oben.
        $html = strstr($html, '<main');
        $krisen = strpos($html, 'id="hilfe-titel"');
        $this->assertNotFalse($krisen);
        $this->assertLessThan(strpos($html, 'id="formular-titel"'), $krisen);
        $this->assertLessThan(strpos($html, 'id="formular-titel"'), strpos($html, '116 016', $krisen));
        $this->assertStringNotContainsString('Für den Notfall', $html);
    }

    /**
     * So sah /anfragen auf der Demo aus: leere Überschrift „Krisendienste“
     * aus der Altseite vor dem Formular, die Nummern darunter.
     */
    public function test_anfragen_auf_dem_stand_der_demo(): void
    {
        $seite = Page::where('slug', 'anfragen')->where('locale', 'de')->sole();
        $seite->update(['titel' => 'Anfragen', 'untertitel' => AnfragenSeiteSeeder::UNTERZEILE_ALT]);
        $seite->blocks()->delete();
        foreach ([
            ['text', ['titel' => AnfragenSeiteSeeder::EINLEITUNG_TITEL, 'absaetze' => AnfragenSeiteSeeder::EINLEITUNG_ALT]],
            ['text', ['titel' => AnfragenSeiteSeeder::ABLAUF_TITEL, 'absaetze' => AnfragenSeiteSeeder::ABLAUF_ALT]],
            ['text', ['titel' => 'Krisendienste', 'absaetze' => ['Diese Stellen stehen dir jederzeit zur Verfügung:']]],
            ['contact_form', ['titel' => 'Kontaktformular']],
            ['hilfe_box', ['titel' => 'Für den Notfall']],
        ] as $i => [$typ, $data]) {
            $seite->blocks()->create(['typ' => $typ, 'position' => $i, 'data' => $data]);
        }

        $migration = require database_path('migrations/2026_10_27_150000_anfragen_und_austausch.php');
        $migration->up();
        $migration->up();

        $this->assertSame(
            ['text', 'text', 'download_list', 'hilfe_box', 'contact_form'],
            $seite->blocks()->orderBy('position')->pluck('typ')->all(),
        );
        $this->assertSame([0, 1, 2, 3, 4], $seite->blocks()->orderBy('position')->pluck('position')->all());
        $this->assertSame('Krisendienste', $seite->blocks()->where('typ', 'hilfe_box')->sole()->data['titel']);
        $this->assertSame(AnfragenSeiteSeeder::ABLAUF, $seite->blocks()->orderBy('position')->skip(1)->first()->data['absaetze']);
        $this->assertSame('Anfragen & Austausch', $seite->fresh()->titel);
        $this->assertSame(AnfragenSeiteSeeder::UNTERZEILE, $seite->fresh()->untertitel);
    }

    /** KEV-277: Taddis Text, „(erforderlich)“ statt „(muss ausgefüllt werden)“. */
    public function test_kontaktformular(): void
    {
        $html = $this->get('/anfragen')->getContent();

        $this->assertStringContainsString('Du darfst uns schreiben, was dich bewegt – in deinen Worten', $html);
        $this->assertStringContainsString('Bitte bedenke: Ohne E-Mail können wir nicht antworten.', $html);
        $this->assertStringContainsString('Dein Anliegen zählt. Auch anonym.', $html);
        $this->assertSame(2, substr_count($html, '(erforderlich)'));
        $this->assertStringNotContainsString('muss ausgefüllt werden', $html);
        $this->assertStringNotContainsString('Du entscheidest, was du schreibst', $html);
    }

    /** KEV-267, Nils' Review: „Kontakt“ nur einmal in den Brotkrumen, auch für Suchmaschinen. */
    public function test_brotkrumen_ohne_doppeltes_kontakt(): void
    {
        $erwartet = [
            '/kontakt' => ['Start', 'Kontakt'],
            '/landesstellen' => ['Start', 'Kontakt', 'Landesstellen'],
            '/anfragen' => ['Start', 'Kontakt', 'Anfragen & Austausch'],
        ];

        foreach ($erwartet as $pfad => $namen) {
            $html = $this->get($pfad)->assertOk()->getContent();

            preg_match('#<script type="application/ld\+json"[^>]*>\s*(\{"@context":"https://schema.org","@type":"BreadcrumbList".*?)\s*</script>#s', $html, $treffer);
            $this->assertSame($namen, array_column(json_decode($treffer[1], true)['itemListElement'], 'name'), $pfad);

            preg_match('#<nav aria-label="Sie sind hier".*?</nav>#s', $html, $nav);
            $this->assertSame(1, substr_count(strip_tags($nav[0]), 'Kontakt'), $pfad);
        }
    }

    /** KEV-275, Nils' Review: Krisendienste und Kontaktformular stehen auch im Verzeichnis „Auf dieser Seite“. */
    public function test_verzeichnis_mit_krisendiensten_und_formular(): void
    {
        $html = $this->get('/anfragen')->assertOk()->getContent();

        preg_match('#<nav aria-label="Auf dieser Seite" class="hidden lg:block" data-verzeichnis>.*?</nav>#s', $html, $nav);
        preg_match_all('#href="\#([^"]+)"#', $nav[0], $ziele);
        $this->assertSame(['hilfe-titel', 'formular-titel'], array_slice($ziele[1], -2));
        $this->assertStringContainsString('id="hilfe-titel"', $html);
        $this->assertStringContainsString('id="formular-titel"', $html);
    }
}
