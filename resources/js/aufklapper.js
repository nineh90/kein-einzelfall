/*
 * Ein Link auf einen Aufklapper klappt ihn auf (KEV-152).
 *
 * /fristen#widerspruch springt zur Karte „Widerspruch“, ein <details>. Ohne
 * dieses Skript stünde sie dort zugeklappt, und wer dem Link gefolgt ist,
 * sähe nur die Überschrift. Gilt für jedes <details> mit Sprungziel, beim
 * Laden und bei jedem Sprung innerhalb der Seite.
 */
export function aufklapperVerdrahten() {
    const oeffnen = () => {
        const id = decodeURIComponent(window.location.hash.slice(1))
        if (!id) return

        const ziel = document.getElementById(id)
        if (ziel instanceof HTMLDetailsElement) ziel.open = true
    }

    oeffnen()
    window.addEventListener('hashchange', oeffnen)
}
