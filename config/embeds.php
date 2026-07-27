<?php

/*
 * Zugelassene Anbieter für eingebettete Inhalte.
 *
 * Diese Liste ist die einzige Stelle, an der Fremdinhalte überhaupt möglich
 * werden: Was hier nicht steht, blockiert die Content-Security-Policy —
 * auch dann, wenn jemand versehentlich einen Einbettungscode in einen
 * Textbaustein kopiert.
 *
 * Geladen wird trotzdem erst nach ausdrücklicher Zustimmung
 * (siehe components/blocks/embed.blade.php). Der Eintrag hier erlaubt nur,
 * er lädt nicht.
 *
 * ⚠️ Vor dem Ergänzen prüfen: Braucht es die Einbettung wirklich, oder tut es
 * auch ein Link? Ein Link überträgt nichts, solange niemand klickt.
 */
return [

    'erlaubte_quellen' => [
        // Spendenwidgets des Vereins. Auf der Altseite laden diese beiden
        // ungefragt — hier nur nach Zustimmung.
        'https://project-widget.betterplace.org',
    ],

];
