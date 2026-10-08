<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** *Sternchen* im Absatz werden fett (KEV-54). */
class HervorhebenTest extends TestCase
{
    public function test_markierter_satz_wird_fett(): void
    {
        $this->assertSame(
            '<strong class="font-semibold text-ink">Erster Satz.</strong> Rest.',
            (string) hervorheben('*Erster Satz.* Rest.'),
        );
    }

    public function test_gendersternchen_und_rechnungen_bleiben(): void
    {
        foreach (['Mitarbeiter*innen und Kolleg*innen', '5 * 3 * 2', 'Fußnote*'] as $text) {
            $this->assertSame(e($text), (string) hervorheben($text));
        }
    }

    public function test_html_im_text_bleibt_harmlos(): void
    {
        $this->assertStringContainsString('&lt;script&gt;', (string) hervorheben('*<script>x</script>*'));
    }

    public function test_ohne_hervorhebung_nimmt_nur_die_sternchen_weg(): void
    {
        $this->assertSame('Satz. Mitarbeiter*innen', ohne_hervorhebung('*Satz.* Mitarbeiter*innen'));
    }

    public function test_links_im_absatz(): void
    {
        $html = (string) hervorheben('Siehe [FSM](/fsm-erweitertes-hilfesystem) und [Teams](https://events.teams.microsoft.com/event/a@b).');

        $this->assertStringContainsString('<a href="/fsm-erweitertes-hilfesystem" class="text-green-deep underline">FSM</a>', $html);
        // Das @ in der Teams-Adresse wird nicht zur E-Mail-Adresse.
        $this->assertStringContainsString('<a href="https://events.teams.microsoft.com/event/a@b" class="text-green-deep underline">Teams</a>', $html);
        $this->assertStringNotContainsString('mailto:', $html);
    }

    public function test_nur_eigene_pfade_und_https_werden_verlinkt(): void
    {
        foreach (['[x](javascript:alert(1))', '[x](http://fremd.example)', '[x](data:text/html,1)'] as $text) {
            $this->assertStringNotContainsString('<a ', (string) hervorheben($text), $text);
        }

        $this->assertStringNotContainsString(' onclick="', (string) hervorheben('[x](/pfad" onclick="alert(1))'));
    }

    public function test_ohne_hervorhebung_entfernt_link_klammern(): void
    {
        $this->assertSame('Text und fett', ohne_hervorhebung('[Text](/x) und *fett*'));
    }
}
