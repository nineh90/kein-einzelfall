/*
 * „IBAN kopieren“ im Spendenbaustein.
 *
 * Der Knopf steht mit hidden im Server-HTML und wird erst hier sichtbar —
 * und nur, wenn der Browser die Zwischenablage anbietet (nicht über http
 * ausser localhost, nicht in manchen eingebetteten Ansichten). Ohne das
 * bliebe ein Knopf, der nichts tut; die IBAN steht ohnehin daneben.
 *
 * Die Rückmeldung läuft über ein role="status": Vorlesehilfen sagen
 * „Kopiert“ an, ohne dass der Fokus springt.
 */

const ANZEIGEDAUER = 2500

export function kopierenVerdrahten() {
    if (!navigator.clipboard?.writeText) return

    document.querySelectorAll('[data-kopieren-bereich]').forEach((bereich) => {
        const knopf = bereich.querySelector('[data-kopieren]')
        const status = bereich.querySelector('[data-kopieren-status]')
        if (!knopf || !status) return

        let zeitgeber
        bereich.hidden = false

        knopf.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(knopf.dataset.kopieren)
            } catch (e) {
                // Abgelehnt (Berechtigung, Fokus weg): lieber still bleiben
                // als „Kopiert“ melden, obwohl nichts in der Ablage liegt.
                return
            }
            status.textContent = status.dataset.text
            clearTimeout(zeitgeber)
            zeitgeber = setTimeout(() => { status.textContent = '' }, ANZEIGEDAUER)
        })
    })
}
