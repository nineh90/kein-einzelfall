/*
 * Bedienung der Darstellungs-Toolbar.
 *
 * Ersetzt das OneTap-WordPress-Plugin der Altseite. Kuratiert statt vollständig:
 * die Funktionen mit echtem Nutzen, ohne die ~200 KB Inline-Ballast pro Seite.
 *
 * Das Panel kommt fertig aus Blade (config/darstellung.php) — hier wird nur
 * verdrahtet. Gearbeitet wird ausschliesslich über data-Attribute und echte
 * Event-Listener, nie über Ausdrücke in HTML-Attributen: Unsere CSP verbietet
 * 'unsafe-eval', und jede Bibliothek, die Attributinhalte zur Laufzeit als Code
 * auswertet, ist damit still ausser Betrieb.
 *
 * Lesen, Speichern und Anwenden liegen in window.keDarstellung. Das wird vom
 * Inline-Skript im <head> bereitgestellt, damit gespeicherte Einstellungen schon
 * vor dem ersten Zeichnen greifen — sonst blitzt bei jedem Seitenaufruf kurz die
 * Standardansicht auf. Für Menschen, die den Kontrastmodus brauchen, ist das
 * kein Schönheitsfehler.
 */

import { speicherKomplettLeeren } from './speicher'

export function toolbarVerdrahten() {
    // Zwei Knöpfe öffnen dasselbe Panel (KEV-26): das Tab am linken Rand ab
    // „lg“ und der Eintrag in der unteren Leiste darunter. Sichtbar ist je
    // Breite nur einer, verdrahtet werden beide.
    const knoepfe = [...document.querySelectorAll('[data-a11y-oeffnen]')]
    const panel = document.getElementById('a11y-panel')
    const api = window.keDarstellung

    if (!knoepfe.length || !panel || !api) return

    // Der Eintrag in der Leiste steht mit hidden im HTML: Ohne dieses Skript
    // wäre er ein Knopf, der nichts tut.
    document.querySelectorAll('[data-a11y-leiste]').forEach((el) => { el.hidden = false })

    let werte = api.lesen()

    // --- Öffnen und Schliessen ---------------------------------------------

    // Der Knopf, der das Panel geöffnet hat — dorthin kehrt der Fokus zurück.
    let ausloeser = knoepfe[0]

    const zeigen = (offen) => {
        panel.hidden = !offen
        for (const k of knoepfe) k.setAttribute('aria-expanded', offen ? 'true' : 'false')
    }

    for (const k of knoepfe) {
        k.addEventListener('click', () => {
            ausloeser = k
            zeigen(panel.hidden)
        })
    }

    panel.querySelector('[data-a11y-schliessen]')?.addEventListener('click', () => {
        zeigen(false)
        ausloeser.focus()
    })

    // Klick daneben schliesst. Die Knöpfe selbst sind ausgenommen, sonst würde
    // ihr eigener Klick das gerade geöffnete Panel sofort wieder zumachen.
    document.addEventListener('click', (e) => {
        if (!panel.hidden && !panel.contains(e.target) && !knoepfe.some((k) => k.contains(e.target))) {
            zeigen(false)
        }
    })

    // Escape schliesst. Stört den Notausgang nicht: der zählt seine drei
    // Tastendrücke unabhängig davon in einem eigenen Listener.
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !panel.hidden) {
            zeigen(false)
            ausloeser.focus()
        }
    })

    // --- Einstellungen ------------------------------------------------------

    const uebernehmen = () => {
        api.anwenden(werte)
        api.speichern(werte)
        spiegeln()
    }

    /** Den gespeicherten Stand in die Bedienelemente zurückschreiben. */
    const spiegeln = () => {
        for (const el of panel.querySelectorAll('[data-a11y-setzen]')) {
            const schluessel = el.dataset.a11ySetzen
            const zahl = el.hasAttribute('data-a11y-zahl')
            const aktuell = werte[schluessel] ?? (zahl ? 0 : '')

            el.setAttribute('aria-pressed', String(aktuell) === el.dataset.a11yWert ? 'true' : 'false')
        }

        for (const el of panel.querySelectorAll('[data-a11y-umschalten]')) {
            el.setAttribute('aria-pressed', werte[el.dataset.a11yUmschalten] ? 'true' : 'false')
        }

        const anzahl = Object.values(werte).filter(Boolean).length
        for (const zaehler of document.querySelectorAll('[data-a11y-zaehler]')) {
            zaehler.textContent = String(anzahl)
            zaehler.hidden = anzahl < 1
        }
    }

    panel.addEventListener('click', (e) => {
        const setzen = e.target.closest('[data-a11y-setzen]')
        if (setzen) {
            const roh = setzen.dataset.a11yWert
            werte[setzen.dataset.a11ySetzen] = setzen.hasAttribute('data-a11y-zahl') ? Number(roh) : roh
            return uebernehmen()
        }

        const umschalten = e.target.closest('[data-a11y-umschalten]')
        if (umschalten) {
            const schluessel = umschalten.dataset.a11yUmschalten
            werte[schluessel] = !werte[schluessel]
            return uebernehmen()
        }

        if (e.target.closest('[data-a11y-zuruecksetzen]')) {
            werte = {}
            uebernehmen()

            /*
             * Der Knopf heisst „Alles zurücksetzen“, also muss er das auch tun.
             *
             * Solange es nur die Darstellungs-Einstellungen gab, stimmte die
             * Beschriftung von selbst. Mit der Trigger-Warnung kam ein zweiter
             * gespeicherter Wert dazu, und sie wurde stillschweigend falsch —
             * genau die Sorte Halbwahrheit, die man einer Zielgruppe nicht
             * zumuten sollte, die auf verlässliche Auskünfte angewiesen ist.
             *
             * Welche Schlüssel es gibt, steht in config/speicher.php und liegt
             * als window.keSpeicher auf jeder Seite bereit.
             */
            speicherKomplettLeeren()

            return
        }
    })

    spiegeln()
}

/**
 * Leselinie folgt dem Zeiger. Der Listener hängt immer, das Element ist per CSS
 * nur sichtbar, wenn die Option gesetzt ist.
 */
export function leselinieVerdrahten() {
    const linie = document.getElementById('leselinie')
    if (!linie) return

    document.addEventListener('mousemove', (e) => {
        linie.style.top = `${e.clientY}px`
    })
}
