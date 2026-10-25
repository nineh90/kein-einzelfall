/*
 * Suche über alle Kategorien „Klartext statt Fachsprache“ (KEV-254).
 *
 * Wie im einzelnen Glossar (glossar.js) filtert die Liste beim Tippen. Die
 * Treffer rechnet aber der Server aus, über 700 Einträge lädt die Seite nicht
 * vorab: Hier wird dieselbe Seite mit ?suche= geholt und nur Trefferzahl und
 * Liste ausgetauscht. Ohne JavaScript schickt das Formular ab wie bisher.
 *
 * Die Adresse bleibt unverändert, wie im Glossar: Was jemand nachschlägt, soll
 * nicht nebenbei im Verlauf landen.
 */

// Ein Buchstabe trifft fast alles; ab zwei wird gesucht.
const MINDESTLAENGE = 2

export function klartextSucheVerdrahten() {
    const formular = document.querySelector('[data-klartext-suche]')
    if (!formular) return

    const feld = formular.querySelector('input[type="search"]')
    const status = document.querySelector('[data-klartext-status]')
    const treffer = document.querySelector('[data-klartext-treffer]')
    const adresse = formular.getAttribute('action').split('#')[0]
    let zeitgeber = null
    let laufend = null

    async function suchen() {
        const gesucht = feld.value.trim()

        laufend?.abort()

        if (gesucht.length < MINDESTLAENGE) {
            status.textContent = ''
            treffer.replaceChildren()
            return
        }

        laufend = new AbortController()

        try {
            const antwort = await fetch(`${adresse}?suche=${encodeURIComponent(gesucht)}`, {
                signal: laufend.signal,
                headers: { Accept: 'text/html' },
            })
            if (!antwort.ok) return

            const seite = new DOMParser().parseFromString(await antwort.text(), 'text/html')
            const neueListe = seite.querySelector('[data-klartext-treffer]')
            const neuerStatus = seite.querySelector('[data-klartext-status]')
            if (!neueListe || !neuerStatus) return

            treffer.replaceChildren(...neueListe.childNodes)
            status.textContent = neuerStatus.textContent
        } catch (fehler) {
            // Abgebrochen, weil weitergetippt wurde: nichts zu tun. Bei einem
            // Netzfehler bleibt die alte Liste stehen; Enter schickt das
            // Formular dann regulär ab.
            if (fehler.name !== 'AbortError') formular.dataset.klartextOffline = '1'
        }
    }

    // Kurz warten, bis das Tippen pausiert, wie im Glossar.
    feld.addEventListener('input', () => {
        clearTimeout(zeitgeber)
        zeitgeber = setTimeout(suchen, 250)
    })

    formular.addEventListener('submit', (ereignis) => {
        if (formular.dataset.klartextOffline) return
        ereignis.preventDefault()
        clearTimeout(zeitgeber)
        suchen()
    })

    // „Suche leeren“ steht in der ausgetauschten Liste, deshalb delegiert.
    treffer.addEventListener('click', (ereignis) => {
        if (!ereignis.target.closest('[data-klartext-leeren]')) return
        ereignis.preventDefault()
        feld.value = ''
        suchen()
        feld.focus()
    })
}
