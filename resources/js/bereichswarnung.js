/*
 * Eigene Triggerwarnung vor einem Bereich (KEV-156, „Raum für Ungesagtes“).
 *
 * Gebaut wie der allgemeine Hinweis (trigger-warnung.js): Im Server-HTML steht
 * ein <dialog open>, hier wird daraus ein echter Dialog mit Fokusfalle und
 * abgedunkeltem Hintergrund.
 *
 * Zwei Unterschiede:
 *
 *   - Gemerkt wird nur für diesen Besuch (sessionStorage), und nur, vor
 *     welchen Bereichen die Warnung bestätigt ist. Ein „nicht mehr anzeigen“
 *     gibt es nicht: Taddi will sie jedes Mal, bevor man weitergeht.
 *   - Steht der allgemeine Hinweis noch offen, wartet diese Warnung, bis er
 *     geschlossen ist. Zwei Dialoge übereinander liest niemand.
 *
 * Auch das ist eine Einstellung auf Wunsch der lesenden Person, ohne Kennung
 * und ohne dass etwas an den Server geht (config/speicher.php).
 */

const SPEICHER = 'ke.bereichswarnung'

function bestaetigt() {
    try {
        const liste = JSON.parse(sessionStorage.getItem(SPEICHER))
        return Array.isArray(liste) ? liste : []
    } catch (e) {
        return []
    }
}

function merken(bereich) {
    const liste = bestaetigt()
    if (liste.includes(bereich)) return

    try {
        sessionStorage.setItem(SPEICHER, JSON.stringify([...liste, bereich]))
    } catch (e) {
        /* Privater Modus o. ä.: Dann kommt die Warnung auf der nächsten Seite wieder. */
    }
}

export function bereichswarnungVerdrahten() {
    const dialog = document.getElementById('bereichswarnung')
    if (!dialog) return

    const wurzel = document.documentElement

    // Das Kopf-Skript hat sie schon versteckt (in diesem Besuch bestätigt).
    // Ganz heraus, damit Vorlesehilfen keinen unsichtbaren Block ankündigen.
    if (wurzel.classList.contains('ke-bereichswarnung-aus')) {
        dialog.remove()
        return
    }

    const bereich = dialog.dataset.bereich

    wurzel.classList.add('ke-bereichswarnung-bereit')

    // Am close-Ereignis, nicht am Klick: ESC schliesst genauso (siehe
    // trigger-warnung.js).
    dialog.addEventListener('close', () => merken(bereich))

    dialog.querySelector('[data-bereichswarnung-weiter]')
        ?.addEventListener('click', () => dialog.close())

    // Kein showModal()? Dann bleibt es beim Block im Seitenfluss über dem
    // Inhalt, mit funktionierenden Knöpfen.
    if (typeof dialog.showModal !== 'function') return

    const zeigen = () => {
        dialog.removeAttribute('open')
        dialog.showModal()
    }

    /*
     * Erst der allgemeine Hinweis, dann dieser. Solange jener offen ist, wird
     * dieser ausgeblendet; er erscheint, sobald jener geschlossen ist.
     * `ke-trigger-aus` zählt nicht als offen: Dann hat trigger-warnung.js den
     * Dialog schon entfernt.
     */
    const allgemein = document.getElementById('trigger-warnung')

    if (allgemein && allgemein.isConnected && allgemein.open) {
        dialog.removeAttribute('open')
        allgemein.addEventListener('close', zeigen, { once: true })
    } else {
        zeigen()
    }
}
