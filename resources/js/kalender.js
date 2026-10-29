/*
 * Blättern im Termin-Kalender, ohne dass die Seite springt.
 *
 * Vor und zurück sind Links (?monat=2026-11#kalender) und gehen auch ohne
 * dieses Skript. Dann lädt aber die ganze Seite neu und springt zum Kalender,
 * er stand danach oben statt dort, wo man geklickt hatte (Kevin: „wirkt
 * komisch“). Hier wird nur der Kalender ausgetauscht. Die Adresse bekommt
 * den Monat, damit Neuladen und Teilen ihn behalten; der Fokus bleibt auf dem
 * Pfeil, Vorlesehilfen hören den neuen Monat.
 */
export function kalenderVerdrahten() {
    const kalender = document.getElementById('kalender')
    if (!kalender) return

    // Bleibt über jeden Austausch hinweg dasselbe Element, sonst sagt eine
    // Vorlesehilfe die Änderung nicht an.
    const ansage = document.createElement('p')
    ansage.className = 'sr-only'
    ansage.setAttribute('aria-live', 'polite')
    kalender.append(ansage)

    kalender.addEventListener('click', async (ereignis) => {
        const link = ereignis.target.closest('a[data-monat]')
        if (!link || ereignis.button !== 0 || ereignis.metaKey || ereignis.ctrlKey || ereignis.shiftKey || ereignis.altKey) return

        ereignis.preventDefault()
        const richtung = link.dataset.monat
        kalender.setAttribute('aria-busy', 'true')

        try {
            const antwort = await fetch(link.href, { credentials: 'same-origin' })
            if (!antwort.ok) throw new Error(antwort.status)

            const neu = new DOMParser().parseFromString(await antwort.text(), 'text/html').getElementById('kalender')
            if (!neu) throw new Error('kein Kalender')

            kalender.replaceChildren(...neu.childNodes, ansage)
            history.replaceState(history.state, '', link.href.split('#')[0])

            ansage.textContent = kalender.querySelector('[data-kalender-monat]')?.textContent.trim() ?? ''
            // Derselbe Pfeil; am Ende des Bereichs gibt es ihn nicht mehr,
            // dann der Monatsname.
            const fokus = kalender.querySelector(`a[data-monat="${richtung}"]`) ?? kalender.querySelector('[data-kalender-monat]')
            fokus?.focus({ preventScroll: true })
        } catch {
            // Lieber einmal neu laden als gar nicht blättern.
            window.location.href = link.href
        } finally {
            kalender.removeAttribute('aria-busy')
        }
    })
}
