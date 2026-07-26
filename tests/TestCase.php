<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /*
     * Die Tests laufen gegen kein_einzelfall_test (siehe phpunit.xml), nicht
     * gegen die Entwicklungsdatenbank. RefreshDatabase legt das Schema zu
     * Beginn des Laufs an und macht jeden Test danach in einer Transaktion
     * rückgängig — kein Test sieht die Daten eines anderen.
     */
    use RefreshDatabase;
}
