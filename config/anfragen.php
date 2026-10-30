<?php

/*
 * Aufbewahrungsfristen für Kontaktanfragen.
 *
 * Vom Verein bestätigt am 30.10.2026 (Taddi: „das passt“). Eine
 * juristische Prüfung gab es nicht, der Verein hat keine Anwälte.
 *
 * Abzuwägen ist:
 *  - kurz genug, um Art. 5 Abs. 1 lit. e DSGVO gerecht zu werden
 *    (Speicherung nur so lange wie nötig)
 *  - lang genug für die tatsächliche Bearbeitung; der Verein arbeitet
 *    ehrenamtlich, da kann eine Anfrage auch mal liegen bleiben
 *  - ob Anfragen mit Bezug zu laufenden Verfahren länger aufbewahrt werden
 *    müssen — dann braucht es dafür ein ausdrückliches Kennzeichen statt
 *    einer pauschal längeren Frist
 *
 * Die Fristen werden von `php artisan anfragen:aufraeumen` durchgesetzt,
 * das täglich läuft (siehe routes/console.php).
 */
return [

    // Nach Abschluss der Bearbeitung
    'aufbewahrung_tage_erledigt' => (int) env('ANFRAGEN_AUFBEWAHRUNG_ERLEDIGT', 90),

    // Ab Eingang, falls die Anfrage nie bearbeitet wurde. Bewusst länger:
    // Es wäre schlimm, jemandem die Nachricht zu löschen, bevor sie jemand
    // gelesen hat.
    'aufbewahrung_tage_offen' => (int) env('ANFRAGEN_AUFBEWAHRUNG_OFFEN', 365),

];
