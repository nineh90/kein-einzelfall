<?php

namespace Database\Seeders;

use App\Models\GlossaryTerm;
use App\Models\Language;
use Illuminate\Database\Seeder;

/**
 * Startbestand des Glossars.
 *
 * Die Begriffe stammen aus der Besprechung vom 02.08.2026, in der der Verein
 * ausdrücklich OEG, SGB XIV, GdB, Pflegegrad und Persönliches Budget als die
 * Begriffe genannt hat, die erklärt gehören.
 *
 * ── Was hier veröffentlicht ist und was nicht ───────────────────────────────
 *
 * Veröffentlicht sind nur die Einträge, deren Text sich auf eine Aussage des
 * Vereins selbst stützt: OEG und SGB XIV stehen wörtlich so im Protokoll,
 * SER und IFG sind die Auflösungen von Abkürzungen aus den eigenen
 * Arbeitsgruppen-Namen. Eine Abkürzung auszuschreiben ist keine Auslegung.
 *
 * Alles Übrige liegt als **Entwurf** in der Datenbank: sichtbar im Panel,
 * unsichtbar auf der Website. Das ist Absicht. Was hier steht, liest jemand,
 * der gerade einen Bescheid in der Hand hält und danach eine Entscheidung
 * trifft — womöglich über eine Frist. Rechtsauskünfte schreiben wir nicht;
 * der Verein hat für genau solche Fälle eigene Anwälte zugesagt (Projektplan,
 * Abschnitt 1). Der Verein prüft die Entwürfe und schaltet sie frei.
 *
 * Steht als Rückfrage auf der Übergabe-Checkliste.
 */
class GlossarSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::eintraege() as $eintrag) {
            GlossaryTerm::updateOrCreate(
                [
                    'locale' => Language::standardCode(),
                    'slug' => $eintrag['slug'],
                ],
                $eintrag + ['published_at' => null],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function eintraege(): array
    {
        return [
            [
                'slug' => 'oeg',
                'kuerzel' => 'OEG',
                'begriff' => 'Opferentschädigungsgesetz',
                // Wörtlich aus dem Besprechungsprotokoll vom 02.08.2026.
                'erklaerung' => 'Das Opferentschädigungsgesetz ist zum 31.12.2023 ausgelaufen und '
                    .'wurde zum 01.01.2024 durch das Sozialgesetzbuch XIV ersetzt. Beide sind '
                    .'weiterhin von Bedeutung: Es gibt Fälle nach altem und nach neuem Recht.',
                'published_at' => now(),
            ],
            [
                'slug' => 'sgb-xiv',
                'kuerzel' => 'SGB XIV',
                'begriff' => 'Sozialgesetzbuch Vierzehntes Buch',
                'erklaerung' => 'Regelt seit dem 01.01.2024 die soziale Entschädigung und hat damit '
                    .'das Opferentschädigungsgesetz abgelöst. Für Taten, die davor liegen, kann '
                    .'weiterhin das alte Recht gelten.',
                'mehr_url' => '/glossar#oeg',
                'mehr_label' => 'Zum Eintrag „OEG“',
                'published_at' => now(),
            ],
            [
                'slug' => 'ser',
                'kuerzel' => 'SER',
                'begriff' => 'Soziales Entschädigungsrecht',
                'erklaerung' => 'Sammelbegriff für die Regelungen, nach denen der Staat Menschen '
                    .'entschädigt, die durch eine Gewalttat oder andere Ereignisse in staatlicher '
                    .'Verantwortung geschädigt wurden. Der Verein arbeitet dazu in der AG 01.',
                'mehr_url' => '/arbeitsgruppen',
                'mehr_label' => 'Zu den Arbeitsgruppen',
                'published_at' => now(),
            ],
            [
                'slug' => 'ifg',
                'kuerzel' => 'IFG',
                'begriff' => 'Informationsfreiheitsgesetz',
                'erklaerung' => 'Gibt jeder Person das Recht, von Behörden Auskunft über deren '
                    .'Unterlagen zu verlangen. Der Verein nutzt es in der AG 02, um die '
                    .'Verwaltungspraxis im sozialen Entschädigungsrecht offenzulegen.',
                'mehr_url' => '/arbeitsgruppen',
                'mehr_label' => 'Zu den Arbeitsgruppen',
                'published_at' => now(),
            ],

            /*
             * Ab hier Entwürfe. Der Verein prüft den Wortlaut und schaltet sie
             * im Panel frei — siehe Klassenkommentar oben.
             */
            [
                'slug' => 'gdb',
                'kuerzel' => 'GdB',
                'begriff' => 'Grad der Behinderung',
                'erklaerung' => 'ENTWURF — bitte prüfen. Masszahl dafür, wie stark sich eine '
                    .'Behinderung auf die Teilhabe am Leben in der Gesellschaft auswirkt. '
                    .'Angegeben in Zehnerschritten von 20 bis 100. Ab 50 gilt jemand als '
                    .'schwerbehindert.',
            ],
            [
                'slug' => 'pflegegrad',
                'begriff' => 'Pflegegrad',
                'erklaerung' => 'ENTWURF — bitte prüfen. Einstufung dafür, wie selbstständig jemand '
                    .'seinen Alltag bewältigen kann. Es gibt fünf Pflegegrade; von ihnen hängt ab, '
                    .'welche Leistungen die Pflegeversicherung übernimmt.',
            ],
            [
                'slug' => 'persoenliches-budget',
                'begriff' => 'Persönliches Budget',
                'erklaerung' => 'ENTWURF — bitte prüfen. Statt einer Sachleistung wird Geld '
                    .'ausgezahlt, mit dem die berechtigte Person die benötigte Unterstützung '
                    .'selbst einkauft und organisiert.',
            ],
        ];
    }
}
