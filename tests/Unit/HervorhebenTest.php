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
}
