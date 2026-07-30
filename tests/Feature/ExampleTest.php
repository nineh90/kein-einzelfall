<?php

namespace Tests\Feature;

use Database\Seeders\StartseiteSeeder;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Die Startseite ist ein Datensatz — ohne ihn ist `/` ein 404.
        $this->seed(StartseiteSeeder::class);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
