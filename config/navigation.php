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
            'label' => 'Verein',
            'url' => '/verein',
            'children' => [
                ['label' => 'Über uns – Vorstand und Team', 'url' => '/ueber-uns-vorstand-und-team'],
                ['label' => 'Satzung',                      'url' => '/satzung'],
                ['label' => 'Mitgliedschaft',               'url' => '/mitgliedschaft'],
                ['label' => 'Istanbul-Konvention',          'url' => '/istanbul-konvention'],
                ['label' => 'Kinderkodex',                  'url' => '/kinderkodex'],
            ],
        ],
        [
            'label' => 'Gruppen & Termine',
            'url' => '/selbsthilfegruppen',
            'children' => [
                ['label' => 'Selbsthilfegruppen',        'url' => '/selbsthilfegruppen'],
                ['label' => 'Arbeitsgruppen',            'url' => '/arbeitsgruppen'],
                ['label' => 'Veranstaltungen',           'url' => '/veranstaltungen'],
                ['label' => 'KE!N EINZELFALL im Dialog', 'url' => '/kein-einzelfall-im-dialog'],
            ],
        ],
        [
            'label' => 'Wissen',
            'url' => '/wissen',
            'children' => [
                ['label' => 'Das Hilfesystem',               'url' => '/das-hilfesystem'],
                ['label' => 'FSM – Erweitertes Hilfesystem', 'url' => '/fsm-erweitertes-hilfesystem'],
                ['label' => 'Erwerbsminderungsrente',        'url' => '/erwerbsminderungsrente'],
                ['label' => 'Das Bürokratie-Labyrinth',      'url' => '/buerokratie-labyrinth'],
                ['label' => 'Traumafolgestörungen verstehen','url' => '/traumafolgestoerungen-verstehen'],
                ['label' => 'Trauma, Bindung und Beziehung', 'url' => '/trauma-bindung-und-beziehung'],
                ['label' => 'Unterstützung',                 'url' => '/unterstuetzung'],
            ],
        ],
        [
            'label' => 'Spenden',
            'url' => '/spenden',
        ],
        [
            'label' => 'Kontakt',
            'url' => '/kontakt',
            'children' => [
                ['label' => 'Kontakt',              'url' => '/kontakt'],
                ['label' => 'Anfragen & Austausch', 'url' => '/anfragen'],
            ],
        ],
    ],

    'footer' => [
        'kontakt' => [
            ['label' => 'kontakt@kein-einzelfall.de', 'url' => 'mailto:kontakt@kein-einzelfall.de'],
            ['label' => 'Anfragen & Austausch',       'url' => '/anfragen'],
        ],
        'informationen' => [
            ['label' => 'Impressum',   'url' => '/impressum'],
            ['label' => 'Datenschutz', 'url' => '/datenschutz'],
            ['label' => 'Barrierefreiheit', 'url' => '/barrierefreiheit'],
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
        ['label' => 'Start',    'url' => '/',                  'icon' => 'home'],
        ['label' => 'Gruppen',  'url' => '/selbsthilfegruppen','icon' => 'users'],
        ['label' => 'Anfrage',  'url' => '/anfragen',          'icon' => 'message'],
    ],

    /*
     * Notausgang. Ziel bewusst unverfänglich und schnell ladend.
     * Die Altseite nutzt google.com; wetter.com ist plausibler als "was jemand
     * gerade angeschaut hat" und verrät weniger als eine leere Suchmaske.
     */
    'exit_url' => 'https://www.wetter.com',
];
