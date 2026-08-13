/*
 * Der vorgeschaltete Hinweis auf belastende Inhalte.
 *
 * Im Server-HTML steht ein <dialog open> — also ein sichtbarer Block ganz oben
 * im Seitenfluss. Hier wird daraus ein echter Dialog: Fokusfalle, abgedunkelter
 * Hintergrund und ESC kommen dabei vom Browser und nicht von uns.
 *
 * Die Richtung ist Absicht. Ein Overlay, das erst JavaScript erzeugt, gibt bei
 * jedem Skriptfehler den Inhalt ungewarnt frei; ein Block, den JavaScript
 * hochstuft, ist im schlechtesten Fall bloss weniger elegant.
 *
 * Gespeichert wird an zwei Orten, und das ist der Unterschied, den der Verein
 * ausdrücklich wollte:
 *   sessionStorage — weggeklickt: kommt beim nächsten Besuch wieder
 *   localStorage   — "nicht mehr anzeigen": bleibt weg
 *
 * Beides ist eine Einstellung auf ausdrücklichen Wunsch der lesenden Person und
 * damit einwilligungsfrei (§ 25 Abs. 2 Nr. 2 TDDDG). Es geht keine einzige
 * Angabe an den Server.
 */

const SPEICHER_DAUERHAFT = 'ke.trigger.aus'
const SPEICHER_SITZUNG = 'ke.trigger.gesehen'

/** Speicherzugriffe scheitern im privaten Modus — das darf die Seite nicht mitreissen. */
function merken(speicher, schluessel) {
    try {
        speicher.setItem(schluessel, '1')
    } catch (e) {
        /* Dann gilt die Entscheidung eben nur für diese Seite. */
    }
}

export function triggerWarnungVerdrahten() {
    const dialog = document.getElementById('trigger-warnung')
    if (!dialog) return

    const wurzel = document.documentElement

    // Das Kopf-Skript hat bereits entschieden, dass der Hinweis nicht zu zeigen
    // ist, und ihn per CSS versteckt, bevor der Browser gezeichnet hat. Jetzt
    // darf er ganz aus dem Dokument — sonst liegt er als unsichtbarer Block im
    // Baum, den manche Vorlesehilfen trotzdem ankündigen.
    if (wurzel.classList.contains('ke-trigger-aus')) {
        dialog.remove()
        return
    }

    const weiter = dialog.querySelector('[data-trigger-weiter]')
    const nie = dialog.querySelector('[data-trigger-nie]')

    // Erst jetzt: Die Knöpfe werden sichtbar, wenn sie verdrahtet sind — nicht
    // schon dann, wenn JavaScript grundsätzlich läuft. Die Regel dazu steht in
    // app.css.
    wurzel.classList.add('ke-trigger-bereit')

    /*
     * Schliessen deckt jeden Weg ab: die beiden Knöpfe, ESC und alles, was der
     * Browser sonst noch anbietet. Deshalb hängt das Merken am close-Ereignis
     * und nicht am Klick — sonst käme der Hinweis nach einem ESC auf der
     * nächsten Seite sofort wieder.
     */
    dialog.addEventListener('close', () => merken(sessionStorage, SPEICHER_SITZUNG))

    weiter?.addEventListener('click', () => dialog.close())

    nie?.addEventListener('click', () => {
        merken(localStorage, SPEICHER_DAUERHAFT)
        dialog.close()
    })

    /*
     * Vom Block zum Dialog. showModal() verlangt, dass das Element nicht schon
     * offen ist — das `open` aus dem Server-HTML muss also erst weg.
     *
     * Kein showModal()? Dann bleibt es beim Block im Seitenfluss. Der Hinweis
     * ist dann weniger aufdringlich, aber vollständig lesbar und die Knöpfe
     * funktionieren — das ist der richtige Ausgang dieses Falls.
     */
    if (typeof dialog.showModal === 'function') {
        dialog.removeAttribute('open')
        dialog.showModal()
    }
}
