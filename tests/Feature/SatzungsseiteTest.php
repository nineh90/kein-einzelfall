<?php

namespace Tests\Feature;

use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Seite „Satzung“ nach Taddis Tickets KEV-193 bis KEV-195.
 */
class SatzungsseiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    private function verzeichnis(): string
    {
        $html = $this->get('/satzung')->assertOk()->getContent();
        $nav = substr($html, strpos($html, 'data-verzeichnis'));

        return substr($nav, 0, strpos($nav, '</nav>'));
    }

    /** KEV-193: Der Abschnitt zum Herunterladen steht im Verzeichnis und springt dorthin. */
    public function test_download_steht_im_verzeichnis(): void
    {
        $html = $this->get('/satzung')->getContent();

        preg_match('#href="\#(dl-[^"]+)"#', $this->verzeichnis(), $treffer);
        $this->assertNotEmpty($treffer);
        $this->assertStringContainsString('<h2 id="'.$treffer[1].'"', $html);
    }
}
