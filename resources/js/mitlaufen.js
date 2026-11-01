/*
 * Mitlaufen neben dem Text (KEV-304).
 *
 * Dokumente, Kalender, Karten und „Auf dieser Seite“ neben einem Text
 * bleiben beim Scrollen unter der Kopfzeile stehen, bis der Text zu Ende ist
 * (CSS: [data-mitlaufen].ist-mitlaufend). Aber nur, wenn sie ganz ins
 * Fenster passen: Ein höheres Element klebte sonst oben fest, und sein
 * unteres Ende käme erst am Schluss des Abschnitts in Sicht. Auf niedrigen
 * Bildschirmen oder mit großer Schrift bleibt es deshalb einfach stehen.
 *
 * Ohne JavaScript steht es oben neben dem Text, ohne mitzulaufen.
 */
const ABSTAND = 48 // Luft oben (1.5rem) und unten

export function mitlaufenVerdrahten() {
    const elemente = [...document.querySelectorAll('[data-mitlaufen]')]

    if (!elemente.length) {
        return
    }

    const pruefen = () => {
        const kopf = document.querySelector('header.sticky')?.offsetHeight ?? 0
        const platz = window.innerHeight - kopf - ABSTAND

        for (const el of elemente) {
            el.classList.toggle('ist-mitlaufend', el.offsetHeight <= platz)
        }
    }

    pruefen()
    window.addEventListener('resize', pruefen, { passive: true })

    if ('ResizeObserver' in window) {
        const beobachter = new ResizeObserver(pruefen)
        elemente.forEach((el) => beobachter.observe(el))
        const kopf = document.querySelector('header.sticky')
        if (kopf) beobachter.observe(kopf)
    }
}
