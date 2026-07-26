/*
 * Bewusst minimal: kein axios, keine SPA-Schicht.
 * Die Seite ist serverseitig gerendert und funktioniert ohne JavaScript.
 * Alpine übernimmt nur progressive Verbesserungen (Menü, A11y-Toolbar).
 *
 * Wichtig: Der Notausgang hängt NICHT an diesem Bundle — der läuft als
 * eigenständiges Inline-Script im <head>. Wenn Vite hier ausfällt, muss er
 * trotzdem funktionieren.
 */
import Alpine from 'alpinejs'
import { EINSTELLUNGEN, lesen, anwenden, speichern } from './a11y'

Alpine.store('menu', {
    offen: false,
    toggle() {
        this.offen = !this.offen
    },
})

Alpine.store('a11y', {
    offen: false,
    werte: lesen(),

    toggle() {
        this.offen = !this.offen
    },

    /** Stufenwerte (Schriftgröße, Zeilenhöhe …) weiterschalten. */
    setzen(schluessel, wert) {
        this.werte[schluessel] = wert
        anwenden(this.werte)
        speichern(this.werte)
    },

    /** Schalter umlegen (Leselinie, große Maus …). */
    umschalten(schluessel) {
        this.setzen(schluessel, !this.werte[schluessel])
    },

    zuruecksetzen() {
        this.werte = {}
        anwenden(this.werte)
        speichern(this.werte)
    },

    aktiv(schluessel, wert = true) {
        return this.werte[schluessel] === wert
    },

    /** Wie viele Einstellungen weichen vom Standard ab? Für den Zähler am Button. */
    get anzahl() {
        return Object.values(this.werte).filter(Boolean).length
    },

    optionen: EINSTELLUNGEN,
})

window.Alpine = Alpine
Alpine.start()

/* Leselinie folgt dem Zeiger. Nur aktiv, wenn die Option gesetzt ist. */
const linie = document.getElementById('leselinie')
if (linie) {
    document.addEventListener('mousemove', (e) => {
        linie.style.top = `${e.clientY}px`
    })
}
