<?php

namespace Tests\Unit;

use App\Models\TeamMember;
use PHPUnit\Framework\TestCase;

/** KEV-63: „Mehr über … lesen“ setzt fort, statt von vorn zu beginnen. */
class ProfilFortsetzungTest extends TestCase
{
    private function person(string $kurz, string $profil): TeamMember
    {
        return new TeamMember(['kurzprofil' => $kurz, 'profil' => $profil]);
    }

    public function test_ganzer_absatz_weiter_mit_dem_naechsten(): void
    {
        $p = $this->person('Erster Satz.', "<p>Erster Satz.</p>\n<p>Zweiter Absatz.</p>");

        $this->assertSame('<p>Zweiter Absatz.</p>', $p->profilFortsetzung());
    }

    public function test_mitten_im_wort_gekuerzt_weiter_am_wortanfang(): void
    {
        $p = $this->person('Wie Dynamiken entste...', "<p>Wie Dynamiken entstehen – und mehr.</p>\n<p>Schluss.</p>");

        $this->assertSame("<p>…entstehen – und mehr.</p>\n<p>Schluss.</p>", $p->profilFortsetzung());
    }

    public function test_absaetze_davor_kommen_danach(): void
    {
        $p = $this->person('Motivation.', "<p>Stichpunkt</p>\n<p>Motivation.</p>\n<p>Weiter.</p>");

        $this->assertSame("<p>Weiter.</p>\n<p>Stichpunkt</p>", $p->profilFortsetzung());
    }

    public function test_ohne_treffer_bleibt_der_ganze_text(): void
    {
        $p = $this->person('Im Panel geändert.', '<p>Ganz anderer Text.</p>');

        $this->assertSame('<p>Ganz anderer Text.</p>', $p->profilFortsetzung());
    }

    public function test_ohne_weiteren_text_kein_aufklapper(): void
    {
        $this->assertFalse($this->person('Nur das.', '<p>Nur das.</p>')->hatProfil());
    }
}
