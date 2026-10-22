/*
 * Suche im Glossar (KEV-153).
 *
 * Ohne JavaScript schickt das Suchfeld ein Formular ab, und der Server zeigt
 * nur die passenden Einträge (?suche=). Hier filtert die Liste schon beim
 * Tippen. Die Adresse bleibt dabei unverändert: Was jemand nachschlägt, soll
 * nicht nebenbei im Verlauf des Browsers landen.
 *
 * Die Regel, was passt, ist dieselbe wie in GlossaryTerm::passt(): die
 * Abkürzung ohne Leerzeichen („idr“ findet „i. d. R.“), der übrige Text mit
 * Wortgrenzen („sgb“ findet nicht „BSG Bundessozialgericht“).
 */

export function vereinfacht(text) {
    return text
        .toLowerCase()
        .replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss')
        .normalize('NFD')
        .replace(/[^a-z0-9]+/g, ' ')
        .trim()
}

const kompakt = (text) => vereinfacht(text).replace(/ /g, '')

export function glossarVerdrahten() {
    const formular = document.querySelector('[data-glossar-suche]')
    if (!formular) return

    const feld = formular.querySelector('input[type="search"]')
    const status = formular.querySelector('[data-glossar-status]')
    const zuruecksetzen = formular.querySelector('[data-glossar-zuruecksetzen]')
    const leer = document.querySelector('[data-glossar-leer]')
    const websuche = document.querySelector('[data-glossar-websuche]')
    const eintraege = [...document.querySelectorAll('[data-glossar-eintrag]')]
    const abschnitte = [...document.querySelectorAll('[data-glossar-abschnitt]')]
    const spruenge = [...document.querySelectorAll('[data-glossar-sprung]')]
    const gesamt = eintraege.length
    let zeitgeber = null

    function filtern() {
        const gesucht = vereinfacht(feld.value)
        let gefunden = 0

        for (const eintrag of eintraege) {
            const passt = gesucht === ''
                || eintrag.dataset.kuerzel.includes(kompakt(gesucht))
                || eintrag.dataset.suchtext.includes(gesucht)
            eintrag.hidden = !passt
            if (passt) gefunden++
        }

        // Buchstaben ohne Treffer verschwinden samt ihrem Sprungpunkt.
        for (const abschnitt of abschnitte) {
            abschnitt.hidden = !abschnitt.querySelector('[data-glossar-eintrag]:not([hidden])')
        }
        for (const sprung of spruenge) {
            const ziel = document.getElementById(sprung.dataset.glossarSprung)?.closest('[data-glossar-abschnitt]')
            sprung.hidden = !ziel || ziel.hidden
        }

        status.textContent = gesucht === ''
            ? formular.dataset.textAlle.replace(':gesamt', gesamt)
            : formular.dataset.textGefiltert.replace(':anzahl', gefunden).replace(':gesamt', gesamt)

        zuruecksetzen.hidden = feld.value.trim() === ''
        leer.hidden = gesucht === '' || gefunden > 0
        if (websuche) websuche.href = websuche.dataset.basis + encodeURIComponent(feld.value.trim())
    }

    // Kurz warten, bis das Tippen pausiert: Sonst meldet die Vorlesehilfe
    // nach jedem Buchstaben eine neue Trefferzahl.
    feld.addEventListener('input', () => {
        clearTimeout(zeitgeber)
        zeitgeber = setTimeout(filtern, 250)
    })

    formular.addEventListener('submit', (ereignis) => {
        ereignis.preventDefault()
        clearTimeout(zeitgeber)
        filtern()
    })

    zuruecksetzen.addEventListener('click', (ereignis) => {
        ereignis.preventDefault()
        feld.value = ''
        filtern()
        feld.focus()
    })
}
