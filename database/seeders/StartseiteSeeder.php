<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Die Startseite als Datensatz.
 *
 * Bis hierher war sie eine feste Blade-Datei — als einzige der 25 Seiten. Der
 * Verein konnte im Panel weder ihre Überschrift noch einen Knopf ändern, und
 * für Übersetzungen gab es sie gar nicht.
 *
 * Alle Texte stammen wörtlich von https://kein-einzelfall.de/ (Stand
 * 26.07.2026). Vertraglich gilt: Inhalte stellt der Verein, wir pflegen sie
 * nur ein. Zwei Abweichungen, beide abgesprochen:
 *
 *   1. Die Überschrift ist jetzt der Satz „Keiner soll mehr sagen müssen …“,
 *      wie im freigegebenen Mockup. Er stand vorher als Unterzeile über den
 *      Einstiegskarten — der Text ist also nicht neu, er ist umgezogen.
 *   2. Die Unterzeile über den Einstiegskarten bleibt deshalb leer. Was
 *      stattdessen dort steht, entscheidet der Verein später.
 *
 * Der Seeder legt die Seite nur an, wenn es sie noch nicht gibt: Nach dem
 * ersten Lauf gehört sie der Redaktion. Ein zweiter Lauf würde sonst
 * überschreiben, was jemand im Panel geändert hat.
 */
class StartseiteSeeder extends Seeder
{
    public function run(): void
    {
        // Muss vorher laufen: Eine Seite ohne Sprache gibt es nicht, und ohne
        // Standardsprache stünde nicht fest, welche das ist. `updateOrCreate`
        // dort macht den zweiten Aufruf unschädlich.
        $this->call(SprachenSeeder::class);

        $locale = Language::standardCode();

        if (Page::where('locale', $locale)->where('slug', Page::STARTSEITE_SLUG)->exists()) {
            $this->command?->info('Startseite steht bereits — unverändert gelassen.');

            return;
        }

        $seite = Page::create([
            'locale' => $locale,
            'slug' => Page::STARTSEITE_SLUG,
            // Der Titel taucht auf der Seite selbst nicht auf — die Überschrift
            // trägt der Aufmacher. Er benennt die Seite im Panel und steht im
            // <title>, dort unverändert wie bisher.
            'titel' => 'Startseite',
            'meta_description' => 'Austausch-/Informationsplattform für (Mit-)Opfer, Angehörige, '
                .'Interessierte + Fachpersonen; zentrales Netzwerk für Sichtbarkeit + Gehör; '
                .'Verein für Opferhilfe.',
            'published_at' => now(),
        ]);

        foreach ($this->bausteine() as $position => $baustein) {
            $seite->blocks()->create([
                'typ' => $baustein['typ'],
                'position' => $position,
                'data' => $baustein['data'],
            ]);
        }

        $this->command?->info('Startseite eingepflegt — '.count($this->bausteine()).' Bausteine.');
    }

    /**
     * Die Struktur folgt dem Bestand der Altseite:
     * Aufmacher · Hilfe-Nummern · Unsere Aufgabe · Vereinsarbeit · Mitglieder ·
     * Spendenaufruf · Kontaktabschluss.
     *
     * @return array<int, array{typ: string, data: array<string, mixed>}>
     */
    private function bausteine(): array
    {
        return [
            [
                'typ' => 'hero',
                'data' => [
                    'eyebrow' => 'Opferhilfe für soziale Gerechtigkeit',
                    // Die Sternchen markieren den Teil mit der handgezeichneten
                    // Linie — im Mockup liegt sie unter dem Zitat.
                    'titel' => 'Keiner soll mehr sagen müssen: *„Ich hab es nicht gewusst!“*',
                    'text' => 'Wir schaffen eine Austausch – und Informationsplattform für Opfer '
                        .'und Mit-Opfer, Angehörige, Interessierte und Fachpersonen. Ein zentrales '
                        .'Netzwerk aus Expertise im Betroffenenkontext, Austausch auf Augenhöhe. '
                        .'Wir leisten Aufklärung und geben Betroffenen eine Stimme. Für mehr '
                        .'Sichtbarkeit und Gehör.',
                    'ctas' => [
                        ['label' => 'Anfragen & Austausch', 'url' => '/anfragen', 'variant' => 'primary'],
                        ['label' => 'Selbsthilfegruppen', 'url' => '/selbsthilfegruppen', 'variant' => 'ghost'],
                    ],
                ],
            ],

            // Von Kevin beauftragt, nicht im Altbestand. Steht bewusst weit
            // oben: wer akut belastet ist, soll nicht scrollen müssen.
            [
                'typ' => 'hilfe_box',
                'data' => [
                    'titel' => 'Du brauchst sofort jemanden zum Reden?',
                    'kompakt' => true,
                ],
            ],

            // Die vier Einstiege der Altseite. Zwei Ziele sind dort kaputt
            // (/selbsthilfegruppen-2/ und /kontaktformular/ liefern 404) —
            // hier auf die richtigen Seiten korrigiert.
            [
                'typ' => 'quick_access',
                'data' => [
                    'titel' => 'Unsere Aufgabe',
                    'sub' => null,
                    'karten' => [
                        [
                            'icon' => 'users',
                            'titel' => 'Selbsthilfegruppen',
                            'text' => 'Der Austausch in unseren Selbsthilfegruppen soll Dir genau '
                                .'da helfen, wo Du Hilfe benötigst, und er soll Dir aufzeigen, dass '
                                .'Du endlich nicht mehr alleine bist, denn wir sind KE!N EINZELFALL! '
                                .'Die Selbsthilfegruppen sind kostenfrei und nicht an eine '
                                .'Mitgliedschaft gebunden.',
                            'url' => '/selbsthilfegruppen',
                            'link' => 'Zu den Selbsthilfegruppen',
                        ],
                        [
                            'icon' => 'message',
                            'titel' => 'Arbeitsgruppen',
                            'text' => 'Um unsere Arbeit weiter zu professionalisieren und gezielt '
                                .'Wirkung zu entfalten, gründen wir immer wieder Arbeitsgruppen – '
                                .'und laden Dich herzlich ein, Teil davon zu werden. Ein '
                                .'Quereinstieg ist möglich, Du kannst jederzeit dazu kommen. Ob mit '
                                .'Fachwissen, Kreativität oder einfach dem Wunsch, etwas zu bewegen!',
                            'url' => '/arbeitsgruppen',
                            'link' => 'Zu den Arbeitsgruppen',
                        ],
                        [
                            'icon' => 'shield',
                            'titel' => 'Anfragen & Austausch',
                            'text' => 'Du wünschst einen persönlichen Austausch in Bezug auf das '
                                .'Soziale Entschädigungsrecht (OEG/SGB XIV), den '
                                .'Schwerbehindertenausweis und/oder den Pflegegrad, oder hast Fragen '
                                .'zu anderen Hilfesystemen, oder möchtest uns etwas mitteilen?',
                            'url' => '/anfragen',
                            'link' => 'Anfrage stellen',
                        ],
                        [
                            'icon' => 'heart',
                            'titel' => 'Spenden',
                            'text' => 'Mit Deiner Spende hilfst Du uns, kostenfreies Wissen und '
                                .'Aufklärung zu leisten, Sichtbarkeit und Gehör zu schaffen, sowie '
                                .'eine Informationsplattform aufzustellen und ein Netzwerk zu bilden.',
                            'url' => '/spenden',
                            'link' => 'Jetzt spenden',
                        ],
                    ],
                ],
            ],

            [
                'typ' => 'text',
                'data' => [
                    'titel' => 'Vereinsarbeit',
                    'absaetze' => [
                        'Der KE!N EINZELFALL e.V. wurde 2024 gegründet – aus einer persönlichen '
                        .'Betroffenheit heraus und mit dem Ziel, von schädigenden Taten betroffene '
                        .'Menschen nicht länger allein zu lassen.',
                    ],
                    'hand' => 'Opferhilfe für soziale Gerechtigkeit!',
                    'cta' => ['label' => 'Mehr über den Verein', 'url' => '/verein', 'variant' => 'primary'],
                ],
            ],

            [
                'typ' => 'text',
                'data' => [
                    'titel' => 'Mitglieder',
                    'absaetze' => [
                        'Jede Mitgliedschaft stärkt unsere Arbeit. Mit jeder Mitgliedschaft wächst '
                        .'unsere Chance auf Veränderung.',
                        'Sei auch Du Teil unseres ständig wachsenden Netzwerks und unterstütze '
                        .'unsere Vision, indem Du Mitglied wirst.',
                    ],
                    'cta' => ['label' => 'Mitglied werden', 'url' => '/mitgliedschaft', 'variant' => 'primary'],
                ],
            ],

            [
                'typ' => 'cta_band',
                'data' => [
                    'eyebrow' => 'Unterstützung',
                    'zitat' => 'Sei Du dabei, jede Unterstützung zählt, egal wie gering!',
                    'ctas' => [
                        ['label' => 'Spenden', 'url' => '/spenden', 'variant' => 'light'],
                        ['label' => 'Mitglied werden', 'url' => '/mitgliedschaft', 'variant' => 'outline'],
                    ],
                ],
            ],

            [
                'typ' => 'contact_close',
                'data' => [
                    'titel' => 'Anfragen & Austausch',
                    'text' => 'Du wünschst einen persönlichen Austausch in Bezug auf das Soziale '
                        .'Entschädigungsrecht (OEG/SGB XIV), den Schwerbehindertenausweis und/oder '
                        .'den Pflegegrad, oder hast Fragen zu anderen Hilfesystemen, oder möchtest '
                        .'uns etwas mitteilen?',
                    // Kein Vereinsinhalt, sondern die Erklärung einer
                    // Bedienfunktion — sonst weiß niemand, dass es den
                    // Tastatur-Kurzbefehl gibt.
                    'hinweis' => 'Du kannst diese Seite jederzeit sofort verlassen: über '
                        .'„Notausgang“ oben rechts, in der Leiste unten, oder mit dreimal ESC.',
                    'ctas' => [
                        ['label' => 'Anfrage stellen', 'url' => '/anfragen', 'variant' => 'primary'],
                        ['label' => 'Kontakt', 'url' => '/kontakt', 'variant' => 'ghost'],
                    ],
                ],
            ],
        ];
    }
}
