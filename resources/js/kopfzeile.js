/*
 * Passt die Kopfzeile nicht mehr in die Breite, wird aus der Menüleiste das
 * Burger-Menü (Prüfung der Firma, 08.10.2026).
 *
 * Die Schriftvergrößerung der Darstellungs-Toolbar skaliert über rem, die
 * Umschaltpunkte (xl = 1280 px) aber nicht. Bei 1280 px und der ersten
 * Schriftstufe lief die Zeile schon 106 px über den Fensterrand, und der
 * Notausgang war nur noch per Seitwärts-Scrollen erreichbar. Feste Grenzen je
 * Stufe wären zerbrechlich (Zeichenabstand, Wortabstand, Kombinationen,
 * längere Übersetzungen), deshalb wird gemessen: Klasse `kopf-eng` auf <html>,
 * das CSS (app.css) blendet dann Menüleiste aus und Burger ein.
 *
 * Ohne JavaScript gibt es die Toolbar nicht, und die Schriftgröße des
 * Browsers verschiebt auch die Umschaltpunkte (die sind in rem).
 */
export function kopfzeileVerdrahten() {
    const zeile = document.querySelector('[data-kopf-zeile]')

    if (!zeile) {
        return
    }

    const html = document.documentElement

    const zuBreit = () => zeile.scrollWidth > zeile.clientWidth + 1

    const pruefen = () => {
        // Erst im normalen Zustand messen, sonst bliebe die Seite nach dem
        // Verkleinern der Schrift im Burger-Modus hängen. Kein Zeichnen
        // dazwischen: Alles passiert im selben Durchlauf.
        html.classList.remove('kopf-eng', 'kopf-sehr-eng')

        // Stufe 1: Burger statt Menüleiste.
        if (zuBreit()) {
            html.classList.add('kopf-eng')
        }

        // Stufe 2, auf dem Handy mit größter Schrift: Notausgang nur noch als
        // Symbol (wie unter 360 px), die Beschriftung bleibt für Vorlesehilfen.
        if (zuBreit()) {
            html.classList.add('kopf-sehr-eng')
        }
    }

    pruefen()

    let geplant = false
    const spaeter = () => {
        if (geplant) return
        geplant = true
        requestAnimationFrame(() => {
            geplant = false
            pruefen()
        })
    }

    window.addEventListener('resize', spaeter)
    document.addEventListener('ke:darstellung', spaeter)
    // Webfonts laden nach: erst dann stimmt die Breite wirklich.
    document.fonts?.ready.then(spaeter)
}
