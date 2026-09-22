<?php

/*
 * Texte der Rahmen-Oberfläche: Kopf, Fuß, Notausgang, Mobil-Leiste,
 * Brotkrumen, Sprachumschalter.
 *
 * Bewusst getrennt von den Inhalten. Diese Texte gehören uns — sie beschreiben
 * die Bedienung. Die Inhalte gehören dem Verein und stehen in der Datenbank.
 * Was hier steht, dürfen wir übersetzen; Inhalte nicht.
 *
 * Deutsch ist die Vorlage: Diese Datei ist der Bestand, wie er vor der
 * Mehrsprachigkeit fest in den Blades stand. Kein Wort wurde umformuliert.
 */
return [

    'sprunglink' => 'Zum Inhalt springen',
    'menue' => 'Menü',
    'hauptnavigation' => 'Hauptnavigation',
    'hauptnavigation_mobil' => 'Hauptnavigation (mobil)',
    'schnellzugriff' => 'Schnellzugriff',
    'sie_sind_hier' => 'Sie sind hier',
    'start' => 'Start',
    'auf_dieser_seite' => 'Auf dieser Seite',
    'alle_ansehen' => 'Alle ansehen',
    'neuer_tab' => '(öffnet in neuem Tab)',

    'notausgang' => [
        'kopf' => 'Notausgang',
        'leiste' => 'Exit',
        // Die Erläuterung steht nur für Vorlesehilfen im Quelltext. Sie muss
        // sagen, was der Knopf tut, bevor jemand ihn drückt.
        'erklaerung' => '– verlässt diese Seite sofort',
    ],

    /*
     * Vorgeschalteter Hinweis auf belastende Inhalte.
     *
     * Die Überschrift und der Text gehören dem Verein und stehen als Seite in
     * der Datenbank. Hier stehen nur die Beschriftungen der Bedienelemente —
     * die beschreiben die Bedienung und gehören uns.
     *
     * „Nicht mehr anzeigen“ ist bewusst nüchtern und ohne Ausrufezeichen:
     * Der Hinweis soll nicht wie eine Hürde wirken, die man wegdrücken muss.
     */
    'trigger' => [
        'eyebrow' => 'Bevor du weiterliest',
        'weiter' => 'Verstanden – weiterlesen',
        'nie_mehr' => 'Diesen Hinweis nicht mehr anzeigen',
        'verlassen' => 'Seite sofort verlassen',
        'ohne_js' => 'Dieser Hinweis lässt sich nur mit eingeschaltetem JavaScript '
            .'ausblenden — er merkt sich die Entscheidung im Browser. Du kannst '
            .'trotzdem einfach weiterlesen.',
    ],

    /*
     * Dokumentenlisten.
     *
     * „extern“ heisst hier: Der Verweis führt zur Behörde selbst. Der Verein
     * hostet Antragsformulare bewusst nicht mehr — Ämter aktualisieren ihre
     * Vordrucke, und wer einen veralteten Antrag einreicht, verliert Zeit,
     * die er oft nicht hat. Entschieden in der Besprechung vom 02.08.2026.
     */
    'dokumente' => [
        'bereich' => 'Dokumente zum Herunterladen',
        'extern' => 'Öffnet :quelle',
        'warum_extern' => 'Anträge und Formulare verlinken wir bei der zuständigen Stelle, '
            .'statt sie hier abzulegen. So bekommst du immer die aktuelle Fassung.',
    ],

    'partner' => [
        'bereich' => 'Partner und Unterstützer',
        'fremde_seite' => '(öffnet eine fremde Seite)',
    ],

    /*
     * Was die Seite im Browser ablegt — und wie man es wieder loswird.
     *
     * Die Liste selbst kommt aus config/speicher.php. Hier stehen nur die
     * Beschriftungen. „Zurücksetzen“ statt „Löschen“: Es geht um eine
     * Einstellung, die man wieder herstellen kann, nicht um etwas, das
     * unwiederbringlich weg ist.
     */
    'speicher' => [
        'titel' => 'Gespeicherte Einstellungen',
        'einleitung' => 'Diese Website merkt sich nur, was in dieser Liste steht, und zwar in deinem Browser '
            .'auf diesem Gerät. Nichts davon geht an uns, nichts davon sagt uns, wer du bist. '
            .'Du kannst jede Einstellung hier wieder zurücksetzen.',
        'zuruecksetzen' => 'Zurücksetzen',
        'alles' => 'Alle Einstellungen zurücksetzen',
        'zustand_gespeichert' => 'Auf diesem Gerät gespeichert',
        'zustand_leer' => 'Nichts gespeichert',
        'zustand_erledigt' => 'Zurückgesetzt. Beim nächsten Seitenaufruf gilt wieder die Voreinstellung.',
        'ohne_js' => 'Ohne JavaScript speichert diese Seite nichts — dann gibt es hier auch '
            .'nichts zurückzusetzen.',
        'darstellung' => [
            'label' => 'Darstellung',
            'text' => 'Schriftgröße, Kontrast, Zeilenabstand und die übrigen Einstellungen '
                .'aus dem Knopf am linken Rand.',
        ],
        'trigger' => [
            'label' => 'Hinweis zu belastenden Inhalten',
            'text' => 'Ob der Hinweis beim Öffnen der Seite erscheint — und ob du ihn '
                .'dauerhaft abbestellt hast.',
        ],
        'spendenhinweis' => [
            'label' => 'Spendenhinweis',
            'text' => 'Wie oft du Seiten aufgerufen hast — der Hinweis kommt erst nach ein paar '
                .'Aufrufen — und ob du ihn weggeklickt hast. Dann bleibt er eine Weile weg.',
        ],
    ],

    'fusszeile' => [
        'kontakt' => 'Kontakt',
        'informationen' => 'Informationen',
        'social' => 'Social Media',
        'umsetzung' => 'Umsetzung:',
    ],

    'weiterlesen' => [
        'mehr_zu' => 'Mehr zu „:bereich“',
        'auch_interessant' => 'Das könnte dich auch interessieren',
    ],

    'sprache' => [
        'auswahl' => 'Sprache',
        'waehlen' => 'Sprache wählen',
        'suchen' => 'Sprache suchen',
        'keine_treffer' => 'Keine Sprache gefunden.',
        'wechseln_zu' => 'Sprache wechseln zu :sprache',
        'aktuell' => 'Aktuelle Sprache: :sprache',
    ],

    /*
     * Sichtbarer Rückfall.
     *
     * Fehlt eine Übersetzung, zeigen wir die Standardsprache — mit Hinweis,
     * statt still eine andere Sprache unterzuschieben. Es geht um Opferrechte,
     * Fristen und Notfallnummern; wer nicht merkt, dass er eine unübersetzte
     * Fassung liest, kann darauf falsche Schlüsse ziehen.
     */
    'entwurf' => [
        'titel' => 'Entwurf — noch nicht vom Verein geprüft.',
        'text' => 'Diese Seite wurde vorbereitet und ist noch nicht gegengelesen. Die Angaben stammen aus amtlichen Quellen, ersetzen aber keine Beratung — verbindlich ist, was die zuständige Stelle in deinem Fall entscheidet.',
    ],

    'rueckfall' => [
        'hinweis' => 'Diese Seite liegt noch nicht auf :ziel vor. '
            .'Sie wird auf :quelle angezeigt.',
    ],

    'fehler' => [
        'titel_404' => 'Diese Seite gibt es nicht',
        'lead_404' => 'Vielleicht hat sich die Adresse geändert, oder es hat sich ein Tippfehler '
            .'eingeschlichen. Hier sind ein paar Wege weiter.',
        'titel_500' => 'Da ist bei uns etwas schiefgegangen',
        'lead_500' => 'Der Fehler liegt nicht bei dir. Versuch es bitte in ein paar Minuten noch '
            .'einmal — die Nummern unten erreichst du davon unabhängig.',
        'titel_503' => 'Wir sind gleich zurück',
        'lead_503' => 'An der Seite wird gerade gearbeitet. Die Nummern unten erreichst du '
            .'davon unabhängig.',
        'suche' => 'Beiträge durchsuchen',
        'suche_knopf' => 'Suchen',
        'wohin' => 'Wohin möchtest du?',
        'zur_startseite' => 'Zur Startseite',
    ],

    /*
     * Spendenhinweis für wiederkehrende Besucherinnen (config/spendenhinweis.php).
     *
     * Der Wortlaut ist ein Vorschlag von uns und steht auf der
     * Übergabe-Checkliste — er ist eine Bitte des Vereins, nicht unsere.
     */
    'spendenhinweis' => [
        'eyebrow' => 'Du bist öfter hier',
        'titel' => 'Hilft dir, was du hier findest?',
        'text' => 'Dann hilf uns, es kostenfrei zu halten — mit einer Spende, egal wie klein.',
        'knopf' => 'Zum Spenden',
        'spaeter' => 'Jetzt nicht',
        'schliessen' => 'Hinweis schliessen',
    ],

    /*
     * Zwei-Klick-Einbettung (components/blocks/embed.blade.php).
     * :anbieter ist der Name des Fremdanbieters, z.B. „betterplace.org“.
     */
    'embed' => [
        'vorher' => 'Dieser Inhalt kommt von :anbieter. Wenn du ihn anzeigst, werden Daten an '
            .':anbieter übertragen — unter anderem deine IP-Adresse. Vorher passiert nichts.',
        'geladen' => 'Inhalt von :anbieter wird angezeigt. Zum Ausblenden erneut auswählen.',
        'anzeigen' => 'Inhalt einmalig anzeigen',
        'ohne_js' => 'Zum Anzeigen dieses Inhalts wird JavaScript benötigt.',
        'ohne_js_direkt' => 'Du kannst ihn auch direkt bei :anbieter öffnen.',
        'direkt' => 'Stattdessen direkt bei :anbieter öffnen',
    ],

    /*
     * Spendenmöglichkeiten.
     *
     * Die Beschriftungen des Bausteins — nicht die Angaben des Vereins (IBAN,
     * Empfänger, Einleitung), die kommen aus dem Datensatz. Der Baustein steht
     * seit KEV-10 auf der Startseite, und die gibt es auch auf Englisch.
     */
    'spenden' => [
        'ueberweisung' => 'Überweisung',
        'empfaenger' => 'Empfänger',
        'paypal_knopf' => 'Bei PayPal spenden',
        'qr_label' => 'QR-Code mit der Bankverbindung des Vereins zum Einlesen in einer Banking-App',
        'qr_hinweis' => 'Mit der Banking-App scannen — die Überweisung ist dann schon ausgefüllt. '
            .'Den Betrag gibst du selbst ein.',
        'projekte' => 'Projekte auf betterplace.org',
        'projekte_hinweis' => 'Für ein bestimmtes Vorhaben spenden.',
        'bescheinigung' => 'Spendenbescheinigung',
    ],

    /*
     * Leichte Sprache.
     *
     * Die Beschriftungen folgen den Regeln der Leichten Sprache: kurze Saetze,
     * keine Fremdwoerter, aktive Formulierung. „Alltagssprache“ statt
     * „Standardfassung“ — „Standard“ ist selbst ein schweres Wort.
     */
    'leichte_sprache' => [
        'name' => 'Leichte Sprache',
        'zu_leichter_sprache' => 'Diese Seite in Leichter Sprache',
        'zur_standardfassung' => 'Diese Seite in Alltags-Sprache',
        'hinweis' => 'Sie lesen die Seite in Leichter Sprache.',
    ],
];
