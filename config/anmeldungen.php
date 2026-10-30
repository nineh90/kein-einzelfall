<?php

/*
 * Anmeldungen zu Selbsthilfegruppen (KEV-226): Aufbewahrung.
 *
 * Vom Verein bestätigt am 30.10.2026 (Taddi: „das passt“), wie die Fristen
 * der Anfragen (config/anfragen.php). Abgewogen: Eine Anmeldung ist eine
 * Gesundheitsangabe (Art. 9 DSGVO) und gehört so kurz wie möglich
 * gespeichert. Nach der Freigabe braucht der Verein sie nur noch, falls
 * jemand nachfragt („habt ihr meine Anmeldung bekommen?“) oder um die
 * Zustimmung zu den Regeln nachzuweisen.
 *
 * Durchgesetzt von `php artisan anmeldungen:aufraeumen` (täglich,
 * routes/console.php).
 */
return [
    // Ab Freigabe oder Ablehnung
    'aufbewahrung_tage_bearbeitet' => (int) env('ANMELDUNGEN_AUFBEWAHRUNG_BEARBEITET', 30),
    // Ab Eingang, falls nie bearbeitet
    'aufbewahrung_tage_offen' => (int) env('ANMELDUNGEN_AUFBEWAHRUNG_OFFEN', 90),
];
