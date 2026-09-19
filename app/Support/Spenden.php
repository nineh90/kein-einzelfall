<?php

namespace App\Support;

use App\Models\Page;
use App\Models\PageBlock;

/**
 * Die Spendenmöglichkeiten des Vereins — an einer Stelle.
 *
 * Konto, PayPal und die betterplace-Projekte stehen auf der Startseite
 * (KEV-10) und auf der Spendenseite (KEV-5). Zwei Kopien derselben IBAN
 * wären zwei Stellen, an denen sie auseinanderlaufen können; deshalb kommen
 * beide Bausteine von hier. Was der Verein danach im Panel ändert, gehört
 * ihm — diese Werte sind der Startbestand, nicht die Wahrheit.
 *
 * Bestand der Altseite (https://kein-einzelfall.de/spenden/, Stand
 * 26.07.2026 und nachgeprüft am 19.09.2026): Deutsche Skatbank, PayPal als
 * Donate-Link, zwei betterplace-Projekte als Widget, Spendenbescheinigung
 * per E-Mail. „WirWunder“ wird dort im Titel genannt, hat aber kein Widget
 * und keinen Link — es gibt nichts zu übernehmen.
 */
class Spenden
{
    /** @return array<string, string> */
    public static function konto(): array
    {
        return [
            'institut' => 'Deutsche Skatbank',
            'iban' => 'DE79 8306 5408 0006 8893 10',
            'bic' => 'GENODEF1SLR',
            // Der Kontoinhaber ist offen (Übergabe-Checkliste): Leer heisst
            // „KE!N EINZELFALL e.V.“ — der Vereinsname, wie er im Impressum
            // steht.
            'verwendungszweck' => 'Spende',
        ];
    }

    /** @return array<string, string> */
    public static function paypal(): array
    {
        return [
            'empfaenger' => 'paypal@kein-einzelfall.de',
            // Der Spendenlink der Altseite, ohne deren HTML-kodiertes „&“.
            'url' => 'https://www.paypal.com/donate?business=paypal@kein-einzelfall.de&currency_code=EUR',
        ];
    }

    /**
     * Die beiden betterplace-Projekte der Altseite. Dort als ungefragt
     * ladende iframes; hier als Zwei-Klick-Einbettung (config/embeds.php
     * erlaubt die Quelle, geladen wird erst nach Zustimmung).
     *
     * @return array<int, array{titel: string, widget: string, url: string}>
     */
    public static function projekte(): array
    {
        return [
            [
                'titel' => 'Onlinepräsenz von KE!N EINZELFALL e.V. – laufende Kosten für Homepage',
                'widget' => 'https://project-widget.betterplace.org/projects/170775?l=de',
                'url' => 'https://www.betterplace.org/de/projects/170775-onlinepraesenz-von-ke-n-einzelfall-e-v-laufende-kosten-fuer-homepage',
            ],
            [
                'titel' => 'Ausstattung für mobile Unterstützung bei Anträgen und Anfragen',
                'widget' => 'https://project-widget.betterplace.org/projects/173993?l=de',
                'url' => 'https://www.betterplace.org/de/projects/173993-ausstattung-fuer-mobile-unterstuetzung-bei-antraegen-und-anfragen',
            ],
        ];
    }

    public const BESCHEINIGUNG_EMAIL = 'verwaltung@kein-einzelfall.de';

    public const BESCHEINIGUNG_TEXT = 'Benötigst du eine Spendenbescheinigung? Dann schreibe uns eine kurze '
        .'E-Mail mit deinen Daten an verwaltung@kein-einzelfall.de und du erhältst eine Bescheinigung.';

    /**
     * Stellt die Spendenseite vom Textblock der Altseite auf den Baustein um.
     *
     * Die Altseite hatte Konto und PayPal als Fliesstext („IBAN: DE79 …“),
     * betterplace als zwei ungefragt ladende iframes, die der Import gar
     * nicht erst mitgenommen hat. Hier wird daraus ein `donation_options`:
     * Konto mit QR-Code, PayPal als Link, betterplace nach Zustimmung,
     * Spendenbescheinigung — an der Stelle, an der der Textblock stand.
     *
     * Gefunden wird über den Inhalt, nicht über die Überschrift: Die
     * englische Fassung heisst „Donate now“, die IBAN darin ist dieselbe.
     * Den Text der Spendenbescheinigung nimmt der Baustein aus dem
     * vorhandenen Block der Seite — in der Sprache, in der er dort steht.
     *
     * Öffentlich und statisch, weil Seeder (frische Datenbank) und Migration
     * (bestehende) dieselbe Umstellung brauchen.
     *
     * @return bool true, wenn umgestellt; false, wenn nichts zu tun war
     */
    public static function spendenseiteUmstellen(Page $seite): bool
    {
        if ($seite->blocks()->where('typ', 'donation_options')->exists()) {
            return false;
        }

        $enthaelt = fn (PageBlock $b, string $suche): bool => $b->typ === 'text'
            && str_contains(json_encode($b->data['absaetze'] ?? [], JSON_UNESCAPED_UNICODE), $suche);

        $bloecke = $seite->blocks()->get();

        $kontoBlock = $bloecke->first(fn ($b) => $enthaelt($b, 'DE79 8306 5408 0006 8893 10'));
        $bescheinigungBlock = $bloecke->first(fn ($b) => $enthaelt($b, self::BESCHEINIGUNG_EMAIL));

        // Kein Kontoblock: Die Seite sieht nicht mehr aus wie der Altbestand —
        // dann hat jemand daran gearbeitet, und wir fassen sie nicht an.
        if (! $kontoBlock) {
            return false;
        }

        $seite->blocks()->create([
            'typ' => 'donation_options',
            'position' => $kontoBlock->position,
            'data' => [
                'titel' => $kontoBlock->data['titel'] ?? 'Jetzt spenden',
                'bank' => self::konto(),
                'paypal' => self::paypal(),
                'projekte' => self::projekte(),
                'bescheinigung' => [
                    'text' => $bescheinigungBlock?->data['absaetze'][0] ?? self::BESCHEINIGUNG_TEXT,
                    'email' => self::BESCHEINIGUNG_EMAIL,
                ],
            ],
        ]);

        $kontoBlock->delete();
        $bescheinigungBlock?->delete();

        return true;
    }
}
