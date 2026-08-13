<?php

namespace Tests\Feature;

use App\Models\GlossaryTerm;
use App\Models\Language;
use Tests\TestCase;

/**
 * Glossar — Abkürzungen und Fachbegriffe.
 *
 * Wunsch aus der Besprechung vom 02.08.2026. Für diese Zielgruppe ist es kein
 * Nachschlagewerk am Rande: Wer einen Bescheid mit „GdB 50“ und „SGB XIV“ in
 * der Hand hält, muss das lesen können, ohne jemanden fragen zu müssen.
 */
class GlossarTest extends TestCase
{
    private function eintrag(array $daten = []): GlossaryTerm
    {
        return GlossaryTerm::create(array_merge([
            'kuerzel' => 'GdB',
            'begriff' => 'Grad der Behinderung',
            'slug' => 'gdb',
            'erklaerung' => 'Masszahl für die Auswirkungen einer Behinderung.',
            'published_at' => now(),
        ], $daten));
    }

    public function test_glossar_ist_erreichbar(): void
    {
        $this->eintrag();

        $this->get('/glossar')
            ->assertOk()
            ->assertSee('Grad der Behinderung')
            ->assertSee('Masszahl für die Auswirkungen einer Behinderung.');
    }

    public function test_jeder_begriff_hat_ein_eigenes_sprungziel(): void
    {
        /*
         * „Siehe /glossar#gdb“ ist genau das, was in einer Antwort des Vereins
         * auf eine Anfrage steht. Ein Glossar, aus dem man nicht auf einen
         * einzelnen Begriff verlinken kann, ist nur die halbe Hilfe.
         */
        $this->eintrag();

        $this->get('/glossar')->assertSee('id="gdb"', false);
    }

    public function test_entwuerfe_stehen_nicht_auf_der_seite(): void
    {
        // Der Startbestand liegt bewusst als Entwurf in der Datenbank: Was hier
        // steht, liest jemand, der danach eine Entscheidung über eine Frist
        // trifft. Rechtsauskünfte schreiben wir nicht.
        $this->eintrag(['kuerzel' => 'XYZ', 'slug' => 'xyz', 'begriff' => 'Ungeprüft', 'published_at' => null]);

        $this->get('/glossar')->assertDontSee('Ungeprüft');
    }

    public function test_die_buchstabenleiste_nennt_nur_belegte_buchstaben(): void
    {
        // Ein ausgegrautes „Q“ nimmt Platz weg und sagt nichts, was die Liste
        // darunter nicht auch sagt.
        $this->eintrag();

        $html = $this->get('/glossar')->getContent();

        $this->assertStringContainsString('href="#buchstabe-g"', $html);
        $this->assertStringNotContainsString('href="#buchstabe-q"', $html);
    }

    public function test_einsortiert_wird_nach_der_abkuerzung(): void
    {
        /*
         * Wer „SGB XIV“ im Bescheid liest, sucht unter S — nicht unter
         * „Sozialgesetzbuch“, und erst recht nicht unter V wie „Vierzehntes“.
         */
        $eintrag = $this->eintrag([
            'kuerzel' => 'SGB XIV',
            'begriff' => 'Zwölftes Beispiel',
            'slug' => 'sgb-xiv',
        ]);

        $this->assertSame('S', $eintrag->anfangsbuchstabe());
    }

    public function test_umlaute_bekommen_kein_eigenes_fach(): void
    {
        // Ein Fach „Ü“ zwischen U und V ist im Deutschen unüblich und lässt
        // Einträge verschwinden, die dort niemand sucht.
        $eintrag = $this->eintrag([
            'kuerzel' => null,
            'begriff' => 'Örtliche Zuständigkeit',
            'slug' => 'oertliche-zustaendigkeit',
        ]);

        $this->assertSame('O', $eintrag->anfangsbuchstabe());
    }

    public function test_abkuerzung_wird_als_abbr_ausgezeichnet(): void
    {
        // Und der ausgeschriebene Begriff steht trotzdem sichtbar daneben:
        // Ein title-Attribut allein ist auf Tastatur und Touch nicht erreichbar.
        $this->eintrag();

        $html = $this->get('/glossar')->getContent();

        $this->assertStringContainsString('<abbr title="Grad der Behinderung"', $html);
        $this->assertStringContainsString('Grad der Behinderung</span>', $html);
    }

    public function test_ohne_uebersetzung_faellt_das_glossar_sichtbar_zurueck(): void
    {
        /*
         * Die Begriffe stammen aus deutschen Gesetzen. Wer sie auf Russisch
         * sucht und nichts findet, braucht trotzdem die deutsche Abkürzung —
         * denn genau die steht in seinem Bescheid.
         */
        Language::where('code', 'en')->update(['aktiv' => true]);
        Language::memoLeeren();

        $this->eintrag();

        $this->get('/en/glossar')
            ->assertOk()
            ->assertSee('Grad der Behinderung')
            ->assertSee('lang="de"', false);
    }

    public function test_leeres_glossar_bricht_nicht(): void
    {
        // Vor dem ersten freigeschalteten Begriff ist die Seite trotzdem
        // erreichbar — sie steht im Menü.
        GlossaryTerm::query()->delete();

        $this->get('/glossar')->assertOk();
    }

    public function test_glossar_steht_in_der_sitemap(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertSee('/glossar', false);
    }
}
