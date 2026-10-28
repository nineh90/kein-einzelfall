/*
 * Mega-Menü und Handy-Menü (Entwurf, 28.10.2026).
 *
 * Beide sind <details> und gehen ohne dieses Skript auf und zu. Es ergänzt,
 * was <details> nicht von selbst kann:
 *
 *  - schliessen bei Klick daneben und bei Esc (danach steht der Fokus wieder
 *    auf dem Knopf, der es geöffnet hat),
 *  - nur einen Bereich offen lassen, auch in Browsern ohne `name`,
 *  - schliessen, sobald jemand die Seite scrollt (Taddi: „wie wenn man
 *    irgendwo hin klickt“). Sonst läge das offene Feld bei kleinem Fenster
 *    über allem, was man gerade lesen will.
 *
 * Geschlossen wird nur bei Gesten, die von der Person selbst kommen: Mausrad,
 * Wischen, Bild-ab- und Leertaste. Nicht beim Ereignis `scroll` selbst: Das
 * feuert auch, wenn der Browser einen fokussierten Link ins Bild holt oder
 * eine Vorlesehilfe die Seite verschiebt, und dann verlöre gerade, wer mit
 * Tastatur oder Screenreader durchs Menü geht, das Menü unter den Fingern.
 * Gesten im offenen Feld selbst (im Handy-Menü scrollt man darin) zählen nicht.
 */
const SCROLLTASTEN = new Set(['PageDown', 'PageUp', ' ', 'Spacebar', 'End', 'Home', 'ArrowDown', 'ArrowUp'])

export function megamenueVerdrahten() {
    const bereiche = [...document.querySelectorAll('details[data-megamenue]')]
    const burger = document.querySelector('details[data-kopf-burger]')
    const alle = burger ? [...bereiche, burger] : bereiche
    if (!alle.length) return

    const offen = () => alle.find((d) => d.open)

    const schliessen = (ausser = null) => {
        for (const d of alle) if (d !== ausser) d.open = false
    }

    for (const b of bereiche) {
        b.addEventListener('toggle', () => { if (b.open) schliessen(b) })
    }

    document.addEventListener('click', (e) => {
        if (!alle.some((d) => d.contains(e.target))) schliessen()
    })

    document.addEventListener('keydown', (e) => {
        const d = offen()
        if (!d) return

        if (e.key === 'Escape') {
            d.open = false
            d.querySelector('summary')?.focus()
            return
        }

        // Scrolltasten nur, wenn der Fokus nicht im Menü steht: Dort bewegen
        // Pfeil- und Leertaste das Menü, nicht die Seite.
        if (SCROLLTASTEN.has(e.key) && !d.contains(document.activeElement)) schliessen()
    })

    const gesteDaneben = (e) => {
        const d = offen()
        if (!d) return
        // Das Feld ist alles im <details> ausser dem Knopf (<summary>)
        const feld = [...d.children].find((k) => k.tagName !== 'SUMMARY')
        if (feld && feld.contains(e.target)) return
        if (e.type === 'wheel' && Math.abs(e.deltaY) < 4) return
        schliessen()
    }

    window.addEventListener('wheel', gesteDaneben, { passive: true })
    window.addEventListener('touchmove', gesteDaneben, { passive: true })
}
