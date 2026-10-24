<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Der Vereinsname in Überschriften am Stück, am Ende in eigener Zeile (KEV-232). */
class UeberschriftTest extends TestCase
{
    private const NAME = '<span class="inline-block">KE!N EINZELFALL e.V.</span>';

    public function test_name_am_ende_steht_in_eigener_zeile(): void
    {
        $this->assertSame('Die Selbsthilfegruppen des<br>'.self::NAME,
            (string) ueberschrift('Die Selbsthilfegruppen des KE!N EINZELFALL e.V.'));
        $this->assertSame('The working groups of<br>'.self::NAME,
            (string) ueberschrift('The working groups of KE!N EINZELFALL e.V.'));
    }

    public function test_name_allein_oder_mittendrin_bricht_nicht_um(): void
    {
        $this->assertSame(self::NAME, (string) ueberschrift('KE!N EINZELFALL e.V.'));
        $this->assertSame('<span class="inline-block">KE!N EINZELFALL</span> im Dialog – Trauma',
            (string) ueberschrift('KE!N EINZELFALL im Dialog – Trauma'));
        $this->assertSame('Hosting', (string) ueberschrift('Hosting'));
    }

    public function test_html_im_text_bleibt_harmlos(): void
    {
        $this->assertSame('&lt;b&gt;x&lt;/b&gt; &amp; '.self::NAME,
            str_replace('<br>', ' ', (string) ueberschrift('<b>x</b> & KE!N EINZELFALL e.V.')));
    }
}
