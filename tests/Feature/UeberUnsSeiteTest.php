<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Seite „Über uns – Vorstand und Team“ nach Taddis Tickets KEV-179 bis
 * KEV-191.
 */
class UeberUnsSeiteTest extends TestCase
{
    use RefreshDatabase;

    private const PFAD = '/ueber-uns-vorstand-und-team';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    /** KEV-181: Das Logo liegt als eigenes Element auf dem Bild, wie bei Verein. */
    public function test_logo_liegt_auf_dem_titelbild(): void
    {
        $this->assertTrue(Titelbilder::logoDarauf(Titelbilder::datei('ueber-uns-vorstand-und-team')));
        Page::where('slug', 'ueber-uns-vorstand-und-team')->update(['titelbild' => Titelbilder::datei('ueber-uns-vorstand-und-team')]);

        $this->get(self::PFAD)->assertOk()->assertSee('data-kopf-logo', false);
    }
}
