<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Aufbewahrungsfristen für Anfragen durchsetzen.
 * Nachts, wenn ohnehin niemand am Panel arbeitet.
 */
Schedule::command('anfragen:aufraeumen')->dailyAt('03:30');
