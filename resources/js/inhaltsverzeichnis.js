/*
 * „Auf dieser Seite“ in der Seitenleiste eines Artikels (x-blocks.artikel):
 * markiert den Abschnitt, in dem man gerade liest.
 *
 * Reine Zugabe. Ohne Skript sind es normale Sprunglinks, und die
 * Markierung fehlt eben. Markiert wird mit aria-current="true", das sagen
 * Vorlesehilfen als „aktuell“ an, und das CSS hängt am selben Attribut.
 *
 * Aktuell ist der letzte Abschnitt, dessen Anfang schon über der oberen
 * Bildschirmkante plus Kopfzeile liegt. Das ist robuster als ein
 * IntersectionObserver auf die Überschriften: Bei langen Abschnitten ist
 * minutenlang keine Überschrift im Bild, und die Markierung soll trotzdem
 * stimmen.
 */

const OBERKANTE = 140 // klebender Kopf plus etwas Luft, in px

export function inhaltsverzeichnisVerdrahten() {
    const nav = document.querySelector('[data-verzeichnis]')
    if (!nav) return

    const eintraege = [...nav.querySelectorAll('a[href^="#"]')]
        .map((link) => ({ link, ziel: document.getElementById(link.hash.slice(1)) }))
        .filter((e) => e.ziel)

    if (!eintraege.length) return

    let geplant = false

    const markieren = () => {
        geplant = false
        let aktuell = eintraege[0]

        for (const e of eintraege) {
            if (e.ziel.getBoundingClientRect().top <= OBERKANTE) aktuell = e
        }

        for (const e of eintraege) {
            if (e === aktuell) e.link.setAttribute('aria-current', 'true')
            else e.link.removeAttribute('aria-current')
        }
    }

    // Höchstens einmal pro Bild neu berechnen, nicht bei jedem Scroll-Ereignis.
    const planen = () => {
        if (geplant) return
        geplant = true
        requestAnimationFrame(markieren)
    }

    window.addEventListener('scroll', planen, { passive: true })
    window.addEventListener('resize', planen)
    markieren()
}
