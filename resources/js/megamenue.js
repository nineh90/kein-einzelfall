/*
 * Mega-Menü (Entwurf, 28.10.2026).
 *
 * Die Bereiche sind <details name="megamenue"> und gehen ohne dieses Skript
 * auf und zu. Es ergänzt, was <details> nicht von selbst kann: schliessen bei
 * Klick daneben und bei Esc (danach steht der Fokus wieder auf dem Bereich),
 * und nur einen Bereich offen lassen, auch in Browsern ohne `name`.
 */
export function megamenueVerdrahten() {
    const bereiche = [...document.querySelectorAll('details[data-megamenue]')]
    if (!bereiche.length) return

    const schliessen = (ausser = null) => {
        for (const b of bereiche) if (b !== ausser) b.open = false
    }

    for (const b of bereiche) {
        b.addEventListener('toggle', () => { if (b.open) schliessen(b) })
    }

    document.addEventListener('click', (e) => {
        if (!bereiche.some((b) => b.contains(e.target))) schliessen()
    })

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return
        const offen = bereiche.find((b) => b.open)
        if (!offen) return
        offen.open = false
        offen.querySelector('summary')?.focus()
    })
}
