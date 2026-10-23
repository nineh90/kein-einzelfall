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
            'children' => [
                ['schluessel' => 'ueber_uns_vorstand_und_team', 'label' => 'Über uns – Vorstand und Team', 'url' => '/ueber-uns-vorstand-und-team'],
                ['schluessel' => 'satzung', 'label' => 'Satzung',                      'url' => '/satzung'],
                ['schluessel' => 'mitgliedschaft', 'label' => 'Mitgliedschaft',               'url' => '/mitgliedschaft'],
                // KEV-97: an Position 4 und 5, Wunsch von Taddi. Seitdem steht
                // Beschwerdemanagement (KEV-98, vorher Position 5) an Position 7.
                ['schluessel' => 'schutz_und_wertekonzept', 'label' => 'Schutz- und Wertekonzept', 'url' => '/schutz-und-wertekonzept'],
                ['schluessel' => 'red_flags', 'label' => 'Red Flags',                    'url' => '/red-flags'],
                ['schluessel' => 'istanbul_konvention', 'label' => 'Istanbul-Konvention',          'url' => '/istanbul-konvention'],
                // KEV-98: an Position 5, Wunsch von Taddi (seit KEV-97 an 7).
                ['schluessel' => 'beschwerdemanagement', 'label' => 'Beschwerdemanagement',         'url' => '/beschwerdemanagement'],
                ['schluessel' => 'kinderkodex', 'label' => 'Kinderkodex',                  'url' => '/kinderkodex'],
                // KEV-103: ohne Positionsangabe von Taddi, deshalb ans Ende.
                ['schluessel' => 'taetigkeits_und_jahresberichte', 'label' => 'Tätigkeits- und Jahresberichte', 'url' => '/taetigkeits-und-jahresberichte'],
                // KEV-105: Platz nicht genannt, deshalb ans Ende.
                ['schluessel' => 'gremium_ukfb', 'label' => 'Gremium UKFB',                 'url' => '/gremium-ukfb'],
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
            'children' => [
                ['schluessel' => 'selbsthilfegruppen', 'label' => 'Selbsthilfegruppen',        'url' => '/selbsthilfegruppen'],
                ['schluessel' => 'arbeitsgruppen', 'label' => 'Arbeitsgruppen',            'url' => '/arbeitsgruppen'],
                ['schluessel' => 'veranstaltungen', 'label' => 'Veranstaltungen',           'url' => '/veranstaltungen'],
                ['schluessel' => 'oeffentlichkeitsarbeit', 'label' => 'Öffentlichkeitsarbeit',     'url' => '/oeffentlichkeitsarbeit'],
                ['schluessel' => 'rueckblick', 'label' => 'Rückblick',                 'url' => '/rueckblick'],
                ['schluessel' => 'aktuelles', 'label' => 'Aktuelles',                 'url' => '/aktuelles'],
                ['schluessel' => 'kein_einzelfall_im_dialog', 'label' => 'KE!N EINZELFALL im Dialog', 'url' => '/kein-einzelfall-im-dialog'],
            ],
        ],
        [
            'schluessel' => 'bereich_wissen',
            'label' => 'Wissen',
            'url' => '/wissen',
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
                ['schluessel' => 'atteste', 'label' => 'Atteste',                       'url' => '/atteste'],
                ['schluessel' => 'glossar', 'label' => 'Glossar',                        'url' => '/glossar'],
                // KEV-155: zweites Glossar, Taddis Datei „3. Behörden und
                // Bescheide“. Ohne Positionsangabe, deshalb neben das Glossar.
                ['schluessel' => 'behoerden_und_bescheide', 'label' => 'Behörden & Bescheide', 'url' => '/behoerden-und-bescheide'],
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
