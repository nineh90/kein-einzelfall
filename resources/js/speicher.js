/*
 * Übersicht und Zurücksetzen dessen, was diese Website im Browser ablegt.
 *
 * Gehört zum Baustein `speicher_uebersicht` auf /barrierefreiheit. Die Liste
 * der Schlüssel kommt aus `config/speicher.php` und steht als JSON am
 * Container — sie wird hier nicht noch einmal aufgeschrieben. Wer künftig etwas
 * speichert, trägt es dort ein und ist hier automatisch dabei.
 *
 * Warum das Zurücksetzen an dieser Stelle wichtig ist: Wer „Hinweis nicht mehr
 * anzeigen“ gewählt hat, kann das sonst nirgends mehr rückgängig machen — auf
 * einem geteilten Gerät ist das ein echtes Problem, und bei dieser Zielgruppe
 * kein theoretisches.
 */

/** Alle Schlüssel eines Eintrags entfernen. Gibt zurück, ob etwas da war. */
export function speicherLeeren(eintrag) {
    let hatteEtwas = false

    for (const [ablage, schluessel] of [
        [localStorage, eintrag.local || []],
        [sessionStorage, eintrag.session || []],
    ]) {
        for (const name of schluessel) {
            try {
                if (ablage.getItem(name) !== null) hatteEtwas = true
                ablage.removeItem(name)
            } catch (e) {
                /* Privater Modus o.ä. — dann war ohnehin nichts gespeichert. */
            }
        }
    }

    return hatteEtwas
}

/** Steht zu diesem Eintrag gerade etwas im Browser? */
function istGespeichert(eintrag) {
    try {
        return (eintrag.local || []).some((n) => localStorage.getItem(n) !== null)
            || (eintrag.session || []).some((n) => sessionStorage.getItem(n) !== null)
    } catch (e) {
        return false
    }
}

/** Sämtliche Schlüssel entfernen, die diese Seite kennt. */
export function speicherKomplettLeeren() {
    Object.values(window.keSpeicher || {}).forEach(speicherLeeren)
}

export function speicherVerdrahten() {
    const wurzel = document.querySelector('[data-speicher]')
    if (!wurzel) return

    // Die Liste steht im Kopf der Seite (config/speicher.php) und damit an
    // derselben Stelle, aus der sich auch das Zurücksetzen in der
    // Darstellungs-Toolbar bedient. Fehlt sie, gibt es nichts zu verdrahten —
    // dann lieber keine Knöpfe als tote.
    const registratur = window.keSpeicher
    if (!registratur) return

    // Erst jetzt werden die Knöpfe sichtbar — wenn sie wirklich verdrahtet
    // sind, nicht schon, wenn JavaScript grundsätzlich läuft (app.css).
    document.documentElement.classList.add('ke-speicher-bereit')

    const texte = {
        gespeichert: wurzel.dataset.speicherTextGespeichert,
        leer: wurzel.dataset.speicherTextLeer,
        erledigt: wurzel.dataset.speicherTextErledigt,
    }

    /*
     * Zustand anzeigen. Ohne diese Rückmeldung drückt jemand „Zurücksetzen“ und
     * sieht nicht, ob etwas passiert ist — bei einer Einstellung, die man nicht
     * sehen kann, ist das der ganze Unterschied.
     */
    const zeigen = (schluessel, meldung = null) => {
        const zeile = wurzel.querySelector(`[data-speicher-eintrag="${schluessel}"]`)
        const status = zeile?.querySelector('[data-speicher-status]')
        const knopf = zeile?.querySelector('[data-speicher-loeschen]')
        if (!status) return

        const belegt = istGespeichert(registratur[schluessel] || {})

        status.textContent = meldung ?? (belegt ? texte.gespeichert : texte.leer)

        // Nichts gespeichert heisst: nichts zurückzusetzen. Der Knopf bleibt
        // sichtbar, wird aber abgeschaltet — verschwände er, wanderte bei jedem
        // Klick die halbe Zeile, und die Tastaturreihenfolge mit ihr.
        if (knopf) knopf.disabled = ! belegt
    }

    const alleZeigen = () => Object.keys(registratur).forEach((s) => zeigen(s))

    wurzel.addEventListener('click', (e) => {
        const einzeln = e.target.closest('[data-speicher-loeschen]')
        if (einzeln) {
            const schluessel = einzeln.dataset.speicherLoeschen
            speicherLeeren(registratur[schluessel] || {})

            // Erst die Meldung, dann der Rest der Zeile: zeigen() setzt den
            // Text sonst gleich wieder auf „nichts gespeichert“.
            zeigen(schluessel, texte.erledigt)
            if (einzeln) einzeln.disabled = true

            return
        }

        if (e.target.closest('[data-speicher-alles]')) {
            Object.values(registratur).forEach(speicherLeeren)
            alleZeigen()

            // Der erste Eintrag trägt die Sammelmeldung — eine Meldung reicht,
            // und eine Vorlesehilfe liest sonst dieselbe Zeile mehrfach vor.
            const erster = Object.keys(registratur)[0]
            if (erster) zeigen(erster, texte.erledigt)
        }
    })

    alleZeigen()
}
