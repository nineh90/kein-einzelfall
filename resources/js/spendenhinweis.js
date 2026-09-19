/*
 * Spendenhinweis für wiederkehrende Besucherinnen (KEV-6).
 *
 * Zählt Seitenaufrufe im Browser und holt den Kasten aus dem Layout hervor,
 * sobald die Schwelle erreicht ist. Weggeklickt heisst: für `ruhe_tage` Ruhe,
 * danach fängt das Zählen von vorn an. Die Zahlen kommen aus
 * config/spendenhinweis.php und stehen als data-Attribute am Kasten.
 *
 * Was hier bewusst NICHT passiert:
 *   - kein Fokuswechsel: Wer liest, liest weiter. Der Kasten ist ein Landmark,
 *     keine Unterbrechung.
 *   - kein ESC zum Schliessen: Dreimal ESC ist der Notausgang. Ein Hinweis,
 *     der auf ESC reagiert, stünde dem im Weg.
 *   - nichts geht an den Server. Ob jemand „wiederkehrt“, weiss nur der
 *     eigene Browser (siehe config/speicher.php, Eintrag `spendenhinweis`).
 *
 * Im privaten Modus scheitert der Speicherzugriff — dann gibt es keinen Zähler
 * und damit auch keinen Hinweis. Genau das ist der richtige Ausgang: Lieber
 * nie fragen als jedes Mal.
 */

const SPEICHER_AUFRUFE = 'ke.spenden.aufrufe'
const SPEICHER_RUHE = 'ke.spenden.ruhe'

const TAG = 24 * 60 * 60 * 1000

/** Ruhe eintragen und den Zähler auf null setzen. */
function ruhen(tage) {
    try {
        localStorage.setItem(SPEICHER_RUHE, String(Date.now() + tage * TAG))
        localStorage.setItem(SPEICHER_AUFRUFE, '0')
    } catch (e) {
        /* Dann gilt die Entscheidung eben nur für diese Seite. */
    }
}

/**
 * Diesen Aufruf zählen und zurückgeben, der wievielte er ist.
 * Null heisst: nicht zählen — Ruhezeit läuft, oder der Speicher ist zu.
 */
function zaehlen() {
    try {
        const ruheBis = parseInt(localStorage.getItem(SPEICHER_RUHE) || '0', 10)

        if (ruheBis > Date.now()) return 0

        // Ruhe abgelaufen: Der Eintrag ist erledigt, der Zähler beginnt neu.
        // Sonst käme der Hinweis am ersten Tag danach sofort wieder.
        if (ruheBis) {
            localStorage.removeItem(SPEICHER_RUHE)
            localStorage.setItem(SPEICHER_AUFRUFE, '0')
        }

        const aufrufe = parseInt(localStorage.getItem(SPEICHER_AUFRUFE) || '0', 10) + 1
        localStorage.setItem(SPEICHER_AUFRUFE, String(aufrufe))

        return aufrufe
    } catch (e) {
        return 0
    }
}

export function spendenHinweisVerdrahten() {
    const kasten = document.querySelector('[data-spendenhinweis]')
    if (!kasten) return

    const ab = parseInt(kasten.dataset.ab, 10) || 5
    const ruheTage = parseInt(kasten.dataset.ruheTage, 10) || 30

    if (zaehlen() < ab) return

    const zeigen = () => { kasten.hidden = false }

    /*
     * Nicht über den Hinweis zu belastenden Inhalten legen. Ist der Dialog
     * offen, wartet der Kasten, bis er geschlossen ist — wer die Warnung
     * noch liest, soll nicht daneben um Geld gebeten werden.
     *
     * `ke-trigger-aus`: Das Kopf-Skript hat den Dialog schon abbestellt, er
     * trägt aber noch `open` aus dem Server-HTML, bis das Bundle ihn entfernt.
     * Das zählt nicht als offen.
     */
    const dialog = document.getElementById('trigger-warnung')
    const dialogOffen = dialog && dialog.open
        && !document.documentElement.classList.contains('ke-trigger-aus')

    if (dialogOffen) {
        dialog.addEventListener('close', zeigen, { once: true })
    } else {
        zeigen()
    }

    const schliessen = () => {
        kasten.hidden = true
        ruhen(ruheTage)
    }

    kasten.querySelectorAll('[data-spendenhinweis-schliessen]')
        .forEach((knopf) => knopf.addEventListener('click', schliessen))

    // Der Weg zur Spendenseite ist Antwort genug — dieselbe Ruhe wie beim
    // Wegklicken, aber ohne den Kasten zu verstecken: Die Seite wechselt ohnehin.
    kasten.querySelector('[data-spendenhinweis-ziel]')
        ?.addEventListener('click', () => ruhen(ruheTage))
}
