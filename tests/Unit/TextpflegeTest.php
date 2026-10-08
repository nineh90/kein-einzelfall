<?php

namespace Tests\Unit;

use App\Support\Textpflege;
use PHPUnit\Framework\TestCase;

class TextpflegeTest extends TestCase
{
    public function test_zusammengeklebte_saetze_bekommen_ein_leerzeichen(): void
    {
        $this->assertSame('hinweisen. Wichtig: Es geht', Textpflege::luecken('hinweisen.Wichtig:Es geht'));
        $this->assertSame('Mitglied werden? Es gibt', Textpflege::luecken('Mitglied werden?Es gibt'));
    }

    public function test_abkuerzungen_und_adressen_bleiben_unberuehrt(): void
    {
        foreach (['KE!N EINZELFALL e.V.', 'z.B. hier', 'kontakt@kein-einzelfall.de', 'TelefonSeelsorge', 'www.kein-einzelfall.de'] as $text) {
            $this->assertSame($text, Textpflege::luecken($text));
        }
    }

    public function test_zusammengeklebte_adresse_wird_drei_zeilen(): void
    {
        $data = Textpflege::bausteinDaten(['absaetze' => ['KE!N EINZELFALL e.V.Schiffbeker Höhe 3022119 Hamburg']], 'impressum');

        $this->assertSame(['KE!N EINZELFALL e.V.', 'Schiffbeker Höhe 30', '22119 Hamburg'], $data['absaetze']);
    }

    public function test_klebende_zwischenueberschrift_wird_eigener_absatz(): void
    {
        $data = Textpflege::bausteinDaten(['absaetze' => ['Dauer der SpeicherungDie Daten werden gelöscht.']], 'datenschutz');

        $this->assertSame(['*Dauer der Speicherung*', 'Die Daten werden gelöscht.'], $data['absaetze']);
    }

    public function test_gesetze_werden_nur_im_alten_wortlaut_ersetzt(): void
    {
        $data = Textpflege::bausteinDaten(['titel' => 'Angaben gemäß § 5 TMG', 'url' => 'https://x.de/§ 5 TMG'], 'impressum');

        $this->assertSame('Angaben gemäß § 5 DDG', $data['titel']);
        $this->assertSame('https://x.de/§ 5 TMG', $data['url'], 'Adressen werden nicht angefasst');
    }
}
