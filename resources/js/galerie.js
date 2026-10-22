/*
 * Fotos einer Messe groß ansehen (KEV-132).
 *
 * Im HTML ist jedes Foto ein Link auf die große Fassung — ohne JavaScript
 * öffnet er einfach das Bild. Hier wird daraus ein Dialog mit Vor und
 * Zurück. Ein natives <dialog> mit showModal(): Es hält den Fokus fest,
 * schließt mit Esc und gibt den Fokus beim Schließen an den Link zurück.
 *
 * Bedienung: Pfeiltasten links und rechts, die Knöpfe im Dialog, Esc oder
 * ein Klick neben das Bild schließen. Die Beschriftungen kommen übersetzt
 * aus den data-Attributen des Galerie-Bereichs.
 */

export function galerieVerdrahten() {
    const galerie = document.querySelector('[data-galerie]')
    if (!galerie) return

    const links = [...galerie.querySelectorAll('a[data-galerie-bild]')]
    if (links.length === 0 || typeof HTMLDialogElement !== 'function') return

    const text = galerie.dataset
    let aktuell = 0
    let ausloeser = null

    const element = (tag, klassen, eigenschaften = {}) => {
        const el = document.createElement(tag)
        el.className = klassen
        Object.assign(el, eigenschaften)
        return el
    }

    // Feste Farben statt der Seitenfarben: Im Dunkel- und Kontrastmodus
    // stellen sich cream und ink um, ein Fotobetrachter bleibt aber dunkel.
    const knopfKlassen = 'flex size-12 items-center justify-center rounded-full bg-white text-2xl leading-none '
        + 'text-neutral-900 shadow hover:bg-neutral-200 focus-visible:outline-2 focus-visible:outline-offset-2 '
        + 'focus-visible:outline-white'

    const dialog = element('dialog', 'm-0 h-full max-h-none w-full max-w-none bg-black/95 p-0 backdrop:bg-black/60')
    dialog.setAttribute('aria-label', text.titel || 'Fotos')

    const rahmen = element('div', 'flex h-full flex-col items-center justify-center gap-3 p-4 sm:p-8')
    const kopf = element('div', 'flex w-full max-w-5xl items-center justify-between gap-3')
    // aria-live: Vorlesehilfen hören beim Blättern „3 von 6“.
    const zaehler = element('p', 'text-sm text-white')
    zaehler.setAttribute('aria-live', 'polite')
    const schliessen = element('button', knopfKlassen, { type: 'button', textContent: '×' })
    schliessen.setAttribute('aria-label', text.schliessen || 'Schließen')
    kopf.append(zaehler, schliessen)

    const figur = element('figure', 'flex min-h-0 w-full max-w-5xl flex-1 flex-col items-center justify-center gap-3')
    const bild = element('img', 'min-h-0 max-h-full max-w-full rounded-card object-contain')
    // Die Beschreibung steht sichtbar darunter; vorgelesen wird sie über alt.
    const unterschrift = element('figcaption', 'max-w-prose text-center text-sm leading-relaxed text-white')
    unterschrift.setAttribute('aria-hidden', 'true')
    figur.append(bild, unterschrift)

    const fuss = element('div', 'flex items-center gap-4')
    const zurueck = element('button', knopfKlassen, { type: 'button', textContent: '‹' })
    zurueck.setAttribute('aria-label', text.zurueck || 'Vorheriges Foto')
    const weiter = element('button', knopfKlassen, { type: 'button', textContent: '›' })
    weiter.setAttribute('aria-label', text.weiter || 'Nächstes Foto')
    fuss.append(zurueck, weiter)

    rahmen.append(kopf, figur, fuss)
    dialog.append(rahmen)
    document.body.append(dialog)

    // Mit nur einem Foto gibt es nichts zu blättern.
    if (links.length === 1) fuss.hidden = true

    function zeigen(index) {
        aktuell = (index + links.length) % links.length
        const vorschau = links[aktuell].querySelector('img')
        bild.src = links[aktuell].href
        bild.alt = vorschau?.alt ?? ''
        unterschrift.textContent = vorschau?.alt ?? ''
        zaehler.textContent = (text.zaehler || ':nr von :gesamt')
            .replace(':nr', String(aktuell + 1))
            .replace(':gesamt', String(links.length))
    }

    links.forEach((link, index) => {
        link.addEventListener('click', (ereignis) => {
            // Neuer Tab, Mittelklick: so lassen, wie der Browser es will.
            if (ereignis.ctrlKey || ereignis.metaKey || ereignis.shiftKey || ereignis.button !== 0) return

            ereignis.preventDefault()
            ausloeser = link
            zeigen(index)
            dialog.showModal()
            schliessen.focus()
        })
    })

    zurueck.addEventListener('click', () => zeigen(aktuell - 1))
    weiter.addEventListener('click', () => zeigen(aktuell + 1))
    schliessen.addEventListener('click', () => dialog.close())

    dialog.addEventListener('keydown', (ereignis) => {
        if (ereignis.key === 'ArrowLeft') zeigen(aktuell - 1)
        if (ereignis.key === 'ArrowRight') zeigen(aktuell + 1)
    })

    // Ein Klick neben das Bild (auf den abgedunkelten Rand) schließt.
    rahmen.addEventListener('click', (ereignis) => {
        if (ereignis.target === rahmen || ereignis.target === figur) dialog.close()
    })

    dialog.addEventListener('close', () => {
        // Zurück zu dem Foto, auf dem man zuletzt war — nicht zu dem, mit dem
        // man geöffnet hat. So landet die Tastatur dort, wo man gerade ist.
        ;(links[aktuell] ?? ausloeser)?.focus()
        bild.removeAttribute('src')
    })
}
