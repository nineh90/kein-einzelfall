<?php

/*
 * Der Spendenhinweis für wiederkehrende Besucherinnen (KEV-6).
 *
 * Wunsch aus der Besprechung vom 02.08.2026: kein „Battle-Button“, kein
 * Aufruf beim ersten Besuch — sondern ein Hinweis, der erst kommt, wenn
 * jemand die Seite schon ein paarmal genutzt hat, und der nach dem Wegklicken
 * längere Zeit Ruhe gibt.
 *
 * Kein Popup im eigentlichen Sinn: ein kleiner Kasten unten am Rand, der
 * weder den Fokus an sich zieht noch die Seite verdeckt oder sperrt. Wer ihn
 * ignoriert, merkt nichts von ihm. Ein modaler Dialog wäre bei dieser
 * Zielgruppe das Gegenteil von unaufdringlich — und läge im schlimmsten Fall
 * über jemandem, der gerade eine Anfrage schreibt.
 *
 * Gezählt wird im Browser (localStorage, siehe config/speicher.php): kein
 * Cookie, keine Kennung, nichts geht an den Server. Ob jemand „wiederkehrt“,
 * weiss nur sein eigener Browser.
 *
 * Die beiden Zahlen sind ein Vorschlag — die Besprechung hat „5 bis 10
 * Seitenaufrufe“ genannt und die Ruhezeit offen gelassen (Übergabe-
 * Checkliste). Hier ändern, nicht im Skript.
 */
return [

    /* Ab dem wievielten Seitenaufruf der Hinweis erscheint. */
    'ab_aufrufen' => 5,

    /*
     * Wie viele Tage nach dem Wegklicken Ruhe ist. Danach beginnt das Zählen
     * von vorn — der Hinweis kommt also nicht am Tag 31 sofort wieder, sondern
     * erst nach weiteren `ab_aufrufen` Seiten.
     */
    'ruhe_tage' => 30,

    /*
     * Seiten, auf denen der Hinweis nie erscheint (Slugs).
     *
     *   spenden          — dort ist er überflüssig
     *   anfragen, kontakt — wer gerade eine Anfrage schreibt, wird nicht um
     *                      Geld gebeten. Das ist keine Höflichkeit, sondern
     *                      der Unterschied zwischen Opferhilfe und Vertrieb.
     *   trigger-warnung  — die Seite des Hinweises selbst
     *
     * Fehlerseiten sind ohnehin ausgenommen (siehe errors/fehlerseite.blade.php).
     */
    'ausgenommen' => ['spenden', 'anfragen', 'kontakt', 'trigger-warnung'],

    /* Wohin der Knopf führt. */
    'ziel' => '/spenden',
];
