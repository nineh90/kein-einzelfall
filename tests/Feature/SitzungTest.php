<?php

namespace Tests\Feature;

use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Was in der Tabelle `sessions` steht, steht in jedem Datenbankabzug.
 * Deshalb: verschlüsselt, und ohne IP-Adresse und Browserkennung.
 */
class SitzungTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // In phpunit.xml steht SESSION_DRIVER=array; hier geht es um die Tabelle.
        config(['session.driver' => 'database']);
        $this->seed(AltseiteSeeder::class);
    }

    public function test_formulartext_nach_fehler_steht_nicht_im_klartext_in_der_tabelle(): void
    {
        $this->withHeader('User-Agent', 'Testbrowser/1.0')
            ->from('/anfragen')
            ->post('/anfrage', [
                'betreff' => 'x',
                'nachricht' => 'Sehr persoenliche Schilderung eines Vorfalls',
                'einwilligung' => '1',
            ])
            ->assertSessionHasErrors('betreff');

        $zeile = DB::table('sessions')->sole();

        $this->assertNull($zeile->ip_address);
        $this->assertNull($zeile->user_agent);
        $this->assertStringNotContainsString('persoenliche Schilderung', base64_decode($zeile->payload));
        $this->assertStringNotContainsString('persoenliche Schilderung', $zeile->payload);
    }
}
