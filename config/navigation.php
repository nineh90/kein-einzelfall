<?php

/*
 * Navigationsstruktur.
 *
 * Die Slugs sind 1:1 aus der WordPress-Altseite übernommen — SEO-Vorgabe des Kunden
 * ("nicht schlechter dastehen als jetzt"). Neu ist nur die Gruppierung: die alte
 * Hierarchie war drei Ebenen tief und inhaltlich unsortiert (z.B. "Wissen" unter
 * "Unterstützung", "Das Hilfesystem" unter "Im Dialog").
 *
 * Wandert später in die Tabelle `navigation_items`, damit die Redaktion sie pflegen kann.
 * Bis dahin ist eine Config die ehrlichere Lösung: keine halbe Abstraktion.
 */
return [

    'main' => [
        [
            'schluessel' => 'bereich_verein',
            'label' => 'Verein',
            'url' => '/verein',
            /*
             * KEV-176: Die Kacheln „Mehr über KE!N EINZELFALL e.V.“ in
             * eigener Reihenfolge, als 3 × 3 spaltenweise gelesen (Taddi:
             * linker, mittlerer, rechter Block). Ohne Gremium UKFB, das steht
             * nur im Menü (KEV-178). Was hier fehlt, erscheint nicht als
             * Kachel.
             */
            'kacheln' => [
                'ueber_uns_vorstand_und_team', 'satzung', 'mitgliedschaft',
                'kinderkodex', 'istanbul_konvention', 'schutz_und_wertekonzept',
                'red_flags', 'beschwerdemanagement', 'taetigkeits_und_jahresberichte',
            ],
            'children' => [
                // Reihenfolge seit KEV-178 (Taddi). Gremium UKFB steht nur hier
                // im Menü, nicht bei den Kacheln (`kacheln` oben, KEV-176).
                ['schluessel' => 'ueber_uns_vorstand_und_team', 'label' => 'Über uns – Vorstand und Team', 'url' => '/ueber-uns-vorstand-und-team'],
                ['schluessel' => 'satzung', 'label' => 'Satzung',                      'url' => '/satzung'],
                ['schluessel' => 'mitgliedschaft', 'label' => 'Mitgliedschaft',               'url' => '/mitgliedschaft'],
                ['schluessel' => 'schutz_und_wertekonzept', 'label' => 'Schutz- und Wertekonzept', 'url' => '/schutz-und-wertekonzept'],
                ['schluessel' => 'red_flags', 'label' => 'Red Flags',                    'url' => '/red-flags'],
                ['schluessel' => 'kinderkodex', 'label' => 'Kinderkodex',                  'url' => '/kinderkodex'],
                ['schluessel' => 'istanbul_konvention', 'label' => 'Istanbul-Konvention',          'url' => '/istanbul-konvention'],
                ['schluessel' => 'gremium_ukfb', 'label' => 'Gremium UKFB',                 'url' => '/gremium-ukfb'],
                ['schluessel' => 'beschwerdemanagement', 'label' => 'Beschwerdemanagement',         'url' => '/beschwerdemanagement'],
                ['schluessel' => 'taetigkeits_und_jahresberichte', 'label' => 'Tätigkeits- und Jahresberichte', 'url' => '/taetigkeits-und-jahresberichte'],
            ],
        ],
        /*
         * KEV-72: „Gruppen & Termine“ heisst jetzt „Gruppen & Veranstaltungen“
         * und hat eine eigene Übersichtsseite, wie „Wissen“. Vorher führte der
         * Menüpunkt auf die Selbsthilfegruppen. Öffentlichkeitsarbeit und
         * Rückblick sind neu, ihr Text folgt in eigenen Tickets. Reihenfolge
         * wie bei Taddi, Aktuelles und „im Dialog“ dahinter wie bisher.
         */
        [
            'schluessel' => 'bereich_gruppen_veranstaltungen',
            'label' => 'Gruppen & Veranstaltungen',
            'url' => '/gruppen-und-veranstaltungen',
            // KEV-225: Auf der Übersicht je zwei Kacheln nebeneinander, Taddis
            // drei Paare (Gruppen, Veranstaltungen und Öffentlichkeitsarbeit,
            // Aktuelles und Rückblick).
            'spalten' => 2,
            'children' => [
                ['schluessel' => 'selbsthilfegruppen', 'label' => 'Selbsthilfegruppen',        'url' => '/selbsthilfegruppen'],
                ['schluessel' => 'arbeitsgruppen', 'label' => 'Arbeitsgruppen',            'url' => '/arbeitsgruppen'],
                // KEV-225: „KE!N EINZELFALL im Dialog“ nicht mehr im Menü und
                // in den Kacheln. Als Veranstaltungsreihe hängt die Seite
                // unter „Veranstaltungen“ und behält so Brotkrumen.
                ['schluessel' => 'veranstaltungen', 'label' => 'Veranstaltungen',           'url' => '/veranstaltungen', 'children' => [
                    ['schluessel' => 'kein_einzelfall_im_dialog', 'label' => 'KE!N EINZELFALL im Dialog', 'url' => '/kein-einzelfall-im-dialog'],
                ]],
                // KEV-238: die drei Themen als eigene Seiten, nicht im Menü
                // aufgeklappt, sondern als Kacheln auf der Übersicht.
                ['schluessel' => 'oeffentlichkeitsarbeit', 'label' => 'Öffentlichkeitsarbeit',     'url' => '/oeffentlichkeitsarbeit', 'children' => [
                    ['schluessel' => 'ehrenamtsmessen', 'label' => 'Ehrenamtsmessen', 'url' => '/ehrenamtsmessen'],
                    ['schluessel' => 'petitionen', 'label' => 'Petitionen', 'url' => '/petitionen'],
                    ['schluessel' => 'presse', 'label' => 'Presse', 'url' => '/presse'],
                ]],
                // KEV-225: Aktuelles vor Rückblick
                ['schluessel' => 'aktuelles', 'label' => 'Aktuelles',                 'url' => '/aktuelles'],
                ['schluessel' => 'rueckblick', 'label' => 'Rückblick',                 'url' => '/rueckblick'],
            ],
        ],
        [
            'schluessel' => 'bereich_wissen',
            'label' => 'Wissen',
            'url' => '/wissen',
            // KEV-246: `vorn` = Taddis Kategorien (Drive „3. Wissen“: Atteste,
            // Formulare, Fristen, Klartext). „Mehr zu „Wissen““ am Seitenende
            // zeigt sie vor den Seiten der alten Website, die Reihenfolge im
            // Menü bleibt.
            'children' => [
                ['schluessel' => 'das_hilfesystem', 'label' => 'Das Hilfesystem',               'url' => '/das-hilfesystem'],
                ['schluessel' => 'fsm_erweitertes_hilfesystem', 'label' => 'FSM – Erweitertes Hilfesystem', 'url' => '/fsm-erweitertes-hilfesystem'],
                ['schluessel' => 'erwerbsminderungsrente', 'label' => 'Erwerbsminderungsrente',        'url' => '/erwerbsminderungsrente'],
                ['schluessel' => 'buerokratie_labyrinth', 'label' => 'Das Bürokratie-Labyrinth',      'url' => '/buerokratie-labyrinth'],
                ['schluessel' => 'traumafolgestoerungen_verstehen', 'label' => 'Traumafolgestörungen verstehen', 'url' => '/traumafolgestoerungen-verstehen'],
                ['schluessel' => 'trauma_bindung_und_beziehung', 'label' => 'Trauma, Bindung und Beziehung', 'url' => '/trauma-bindung-und-beziehung'],
                ['schluessel' => 'unterstuetzung', 'label' => 'Unterstützung',                 'url' => '/unterstuetzung'],
                // KEV-144: ohne Positionsangabe von Taddi, deshalb ans Ende,
                // aber vor das Glossar, das als Nachschlagewerk zuletzt steht.
                ['schluessel' => 'atteste', 'label' => 'Atteste',                       'url' => '/atteste', 'vorn' => true],
                // KEV-150: Taddis „4. Formulare“, zwischen Atteste (1) und
                // Fristen (5).
                ['schluessel' => 'formulare', 'label' => 'Formulare', 'url' => '/formulare', 'vorn' => true],
                // KEV-152: Taddis „5. Fristen“, nach Atteste und vor den
                // Glossaren, die als Nachschlagewerke zuletzt stehen.
                ['schluessel' => 'fristen', 'label' => 'Fristen und Möglichkeiten', 'url' => '/fristen', 'vorn' => true],
                // KEV-254: Taddis „Klartext statt Fachsprache“ mit ihren
                // Kategorien 1 bis 6 (die 7, „Typische Sätze“, steckt in 3
                // bis 6). Nicht im Menü aufgeklappt, sondern als Kacheln auf
                // der Übersicht, darüber eine Suche über alle Kategorien.
                // Nachschlagewerk, deshalb zuletzt.
                ['schluessel' => 'klartext_statt_fachsprache', 'label' => 'Klartext statt Fachsprache', 'url' => '/klartext-statt-fachsprache', 'vorn' => true, 'children' => [
                    // KEV-153, seit KEV-254 mit Taddis Namen
                    ['schluessel' => 'glossar', 'label' => 'Abkürzungen im Sozialrecht', 'url' => '/glossar'],
                    // KEV-154
                    ['schluessel' => 'abkuerzungen_im_sexuellen_kontext', 'label' => 'Abkürzungen im sexuellen Kontext', 'url' => '/abkuerzungen-im-sexuellen-kontext'],
                    // KEV-155
                    ['schluessel' => 'behoerden_und_bescheide', 'label' => 'Behörden & Bescheide', 'url' => '/behoerden-und-bescheide'],
                    // KEV-259
                    ['schluessel' => 'gericht_und_verfahren', 'label' => 'Gericht & Verfahren', 'url' => '/gericht-und-verfahren'],
                    // KEV-260
                    ['schluessel' => 'medizinische_berichte_und_gutachten', 'label' => 'Medizinische Berichte & Gutachten', 'url' => '/medizinische-berichte-und-gutachten'],
                    // KEV-261
                    ['schluessel' => 'sozialrecht_und_hilfesystem', 'label' => 'Sozialrecht & Hilfesystem', 'url' => '/sozialrecht-und-hilfesystem'],
                ]],
            ],
        ],
        /*
         * KEV-159: neuer Bereich, Platz wie in Taddis Drive („4. Raum für
         * Ungesagtes“ nach „3. Wissen“). Unterseiten in Taddis Reihenfolge
         * (Bilder 1. bis 4., KEV-156): Wall of Shame (KEV-157) ist Nummer 1,
         * Wort für Wort (KEV-156) Nummer 2, Blickwinkel (KEV-158) Nummer 3,
         * Unüberhörbar Nummer 4.
         */
        [
            'schluessel' => 'bereich_raum_fuer_ungesagtes',
            'label' => 'Raum für Ungesagtes',
            'url' => '/raum-fuer-ungesagtes',
            'children' => [
                ['schluessel' => 'wall_of_shame', 'label' => 'Wall of Shame', 'url' => '/wall-of-shame'],
                ['schluessel' => 'wort_fuer_wort', 'label' => 'Wort für Wort', 'url' => '/wort-fuer-wort'],
                ['schluessel' => 'blickwinkel', 'label' => 'Blickwinkel', 'url' => '/blickwinkel'],
                ['schluessel' => 'unueberhoerbar', 'label' => 'Unüberhörbar', 'url' => '/unueberhoerbar'],
            ],
        ],
        /*
         * KEV-160: eigener Bereich, Platz wie in Taddis Drive („5. Projekte“
         * nach „4. Raum für Ungesagtes“). Die Projekte stehen als Unterseiten
         * darunter; die Übersicht zeigt sie als Kacheln.
         */
        [
            'schluessel' => 'bereich_projekte',
            'label' => 'Projekte',
            'url' => '/projekte',
            'children' => [
                // KEV-162, das erste Projekt. Reihenfolge wie die Projektliste
                // des Vereins (VVT 4.5): ANUAS 4.5.12, § 109 SGG 4.5.21.
                ['schluessel' => 'anuas', 'label' => 'ANUAS', 'url' => '/anuas'],
                // KEV-161
                ['schluessel' => 'paragraf_109_sgg', 'label' => '§ 109 SGG Gegengutachten', 'url' => '/109-sgg-gegengutachten'],
            ],
        ],
        [
            'schluessel' => 'bereich_spenden',
            'label' => 'Spenden',
            'url' => '/spenden',
        ],
        [
            'schluessel' => 'bereich_kontakt',
            'label' => 'Kontakt',
            'url' => '/kontakt',
            'children' => [
                ['schluessel' => 'kontakt', 'label' => 'Kontakt',              'url' => '/kontakt'],
                // KEV-104: kein Platz im Ticket. Hier, weil die Kontaktseite
                // „Alle Ansprechpartner, Landesstellen …“ verspricht.
                ['schluessel' => 'landesstellen', 'label' => 'Landesstellen',        'url' => '/landesstellen'],
                ['schluessel' => 'anfragen', 'label' => 'Anfragen & Austausch', 'url' => '/anfragen'],
            ],
        ],
    ],

    'footer' => [
        /*
         * Die Postfächer des Vereins (KEV-124). „Anfragen & Austausch“ stand
         * bis dahin hier, ist aber über das Menü unter Kontakt erreichbar.
         * Arbeitsgruppen in der Einzahl, wie in jedem AG-Text (Group::AG_ADRESSE).
         */
        'kontakt' => [
            ['label' => 'kontakt@kein-einzelfall.de',       'url' => 'mailto:kontakt@kein-einzelfall.de'],
            ['label' => 'selbsthilfe@kein-einzelfall.de',   'url' => 'mailto:selbsthilfe@kein-einzelfall.de'],
            ['label' => 'arbeitsgruppe@kein-einzelfall.de', 'url' => 'mailto:arbeitsgruppe@kein-einzelfall.de'],
            ['label' => 'presse@kein-einzelfall.de',        'url' => 'mailto:presse@kein-einzelfall.de'],
        ],
        'informationen' => [
            ['schluessel' => 'impressum', 'label' => 'Impressum',   'url' => '/impressum'],
            ['schluessel' => 'datenschutz', 'label' => 'Datenschutz', 'url' => '/datenschutz'],
            ['schluessel' => 'barrierefreiheit', 'label' => 'Barrierefreiheit', 'url' => '/barrierefreiheit'],
            /*
             * Der Weg zurück.
             *
             * Diese Seite legt zwei Einstellungen im Browser ab (siehe
             * config/speicher.php). Wer eine davon gewählt hat — vor allem
             * „Hinweis nicht mehr anzeigen“ —, muss sie auch wieder loswerden
             * können, ohne die Browser-Einstellungen zu durchsuchen. Auf einem
             * geteilten Gerät ist das kein theoretisches Problem.
             *
             * Springt in den Abschnitt am Ende von /barrierefreiheit statt auf
             * eine eigene Seite: Dort steht schon, was die Website tut, und die
             * Auskunft gehört zusammen mit dem Schalter dazu an einen Ort.
             */
            [
                'schluessel' => 'gespeicherte_einstellungen',
                'label' => 'Gespeicherte Einstellungen',
                'url' => '/barrierefreiheit#gespeicherte-einstellungen',
            ],
        ],
        /*
         * Reine Links, keine eingebetteten Zeitleisten oder Zählwerke: Solange
         * niemand klickt, erfährt keine dieser Plattformen etwas von diesem
         * Besuch.
         *
         * Discord fehlt bewusst. In der Besprechung vom 02.08.2026 hat der
         * Verein die Plattform selbst infrage gestellt — die Ausweis-
         * verifizierung widerspricht dem Anonymitätsanspruch. Erst entscheiden,
         * dann verlinken.
         */
        'social' => [
            ['label' => 'Instagram', 'url' => 'https://www.instagram.com/kein_einzelfall_opferhilfe'],
            ['label' => 'Facebook',  'url' => 'https://www.facebook.com/profile.php?id=61563326728055'],
            ['label' => 'TikTok',    'url' => 'https://www.tiktok.com/@kein_einzelfall.de'],
            /*
             * YouTube fehlt noch. Der Kanal wurde in der Besprechung genannt,
             * seine Adresse aber nicht — und eine geratene Adresse ist
             * schlechter als keine: Sie führt entweder ins Leere oder, im
             * schlimmeren Fall, zu einem fremden Kanal. Steht als Rückfrage auf
             * der Übergabe-Checkliste; hier nur die Zeile eintragen:
             *
             * ['label' => 'YouTube', 'url' => 'https://www.youtube.com/@…'],
             */
        ],
    ],

    /*
     * Mobile Sticky-Bar. Bewusst wenige Einträge — die Bar ist in akuten
     * Situationen die primäre Navigation, da zählt Eindeutigkeit vor Vollständigkeit.
     *
     * Zusammen mit „Darstellung“ und dem Notausgang (beide fest in
     * mobile-bar.blade.php) sind es vier. „Start“ ist seit KEV-28 raus: Mit
     * fünf Symbolen wurde die Leiste auf kleinen Handys eng, und zur
     * Startseite führt ohnehin das Logo oben links, auf jeder Seite.
     */
    'mobile_bar' => [
        ['schluessel' => 'leiste_gruppen', 'label' => 'Gruppen',  'url' => '/selbsthilfegruppen', 'icon' => 'users'],
        ['schluessel' => 'leiste_anfrage', 'label' => 'Anfrage',  'url' => '/anfragen',          'icon' => 'message'],
    ],

    /*
     * Notausgang. Ziel bewusst unverfänglich und schnell ladend, und immer
     * eine fremde Domain: Eine Seite unter kein-einzelfall.de verriete in
     * Adresszeile und Verlauf, wo man war.
     *
     * Seit KEV-40 Google, Wunsch des Vereins (wie auf der Altseite). Vorher
     * wetter.com. Gilt für alle Wege hinaus: Notausgang oben, „Exit“ in der
     * unteren Leiste, „Seite sofort verlassen“ im Hinweisfenster und dreimal
     * Esc (exit-script.blade.php).
     */
    'exit_url' => 'https://www.google.de',
];
