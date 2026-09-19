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
 *   localStorage   — Kästchen "nicht mehr anzeigen" war angekreuzt: bleibt weg
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

    // Erst jetzt: Knopf und Kästchen werden sichtbar, wenn sie verdrahtet sind —
    // nicht schon dann, wenn JavaScript grundsätzlich läuft. Die Regel dazu
    // steht in app.css.
    wurzel.classList.add('ke-trigger-bereit')

    /*
     * Alles hängt am close-Ereignis, nicht am Klick.
     *
     * Schliessen geht auf mehreren Wegen: über den Knopf, über ESC und über
     * alles, was der Browser sonst noch anbietet. Am Klick zu horchen hiesse,
     * dass ein ESC nichts merkt — der Hinweis käme auf der nächsten Seite
     * sofort wieder. Und wer das Kästchen ankreuzt und dann ESC drückt, hat
     * seine Entscheidung genauso getroffen wie jemand, der den Knopf trifft.
     */
    dialog.addEventListener('close', () => {
        if (nie?.checked) {
            merken(localStorage, SPEICHER_DAUERHAFT)
        }

        merken(sessionStorage, SPEICHER_SITZUNG)
    })

    weiter?.addEventListener('click', () => dialog.close())

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
