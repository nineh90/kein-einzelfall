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
                ['schluessel' => 'istanbul_konvention', 'label' => 'Istanbul-Konvention',          'url' => '/istanbul-konvention'],
                ['schluessel' => 'kinderkodex', 'label' => 'Kinderkodex',                  'url' => '/kinderkodex'],
            ],
        ],
        [
            'schluessel' => 'bereich_gruppen_termine',
            'label' => 'Gruppen & Termine',
            'url' => '/selbsthilfegruppen',
            'children' => [
                ['schluessel' => 'selbsthilfegruppen', 'label' => 'Selbsthilfegruppen',        'url' => '/selbsthilfegruppen'],
                ['schluessel' => 'arbeitsgruppen', 'label' => 'Arbeitsgruppen',            'url' => '/arbeitsgruppen'],
                ['schluessel' => 'veranstaltungen', 'label' => 'Veranstaltungen',           'url' => '/veranstaltungen'],
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
                ['schluessel' => 'anfragen', 'label' => 'Anfragen & Austausch', 'url' => '/anfragen'],
            ],
        ],
    ],

    'footer' => [
        'kontakt' => [
            ['label' => 'kontakt@kein-einzelfall.de', 'url' => 'mailto:kontakt@kein-einzelfall.de'],
            ['schluessel' => 'anfragen', 'label' => 'Anfragen & Austausch',       'url' => '/anfragen'],
        ],
        'informationen' => [
            ['schluessel' => 'impressum', 'label' => 'Impressum',   'url' => '/impressum'],
            ['schluessel' => 'datenschutz', 'label' => 'Datenschutz', 'url' => '/datenschutz'],
            ['schluessel' => 'barrierefreiheit', 'label' => 'Barrierefreiheit', 'url' => '/barrierefreiheit'],
        ],
        'social' => [
            ['label' => 'Instagram', 'url' => 'https://www.instagram.com/kein_einzelfall_opferhilfe'],
            ['label' => 'Facebook',  'url' => 'https://www.facebook.com/profile.php?id=61563326728055'],
            ['label' => 'TikTok',    'url' => 'https://www.tiktok.com/@kein_einzelfall.de'],
        ],
    ],

    /*
     * Mobile Sticky-Bar. Bewusst genau vier Einträge — die Bar ist in akuten
     * Situationen die primäre Navigation, da zählt Eindeutigkeit vor Vollständigkeit.
     */
    'mobile_bar' => [
        ['schluessel' => 'leiste_start', 'label' => 'Start',    'url' => '/',                  'icon' => 'home'],
        ['schluessel' => 'leiste_gruppen', 'label' => 'Gruppen',  'url' => '/selbsthilfegruppen', 'icon' => 'users'],
        ['schluessel' => 'leiste_anfrage', 'label' => 'Anfrage',  'url' => '/anfragen',          'icon' => 'message'],
    ],

    /*
     * Notausgang. Ziel bewusst unverfänglich und schnell ladend.
     * Die Altseite nutzt google.com; wetter.com ist plausibler als "was jemand
     * gerade angeschaut hat" und verrät weniger als eine leere Suchmaske.
     */
    'exit_url' => 'https://www.wetter.com',
];
