/*
 * Höhe der Kopfzeile als CSS-Variable --kopfhoehe (KEV-29).
 *
 * Der Aufmacher der Startseite soll genau das Fenster minus Kopfzeile füllen.
 * Die Kopfzeile ist aber keine feste Zahl: Rahmen, Zeilenhöhe und vor allem
 * die Schriftvergrößerung der Darstellungs-Toolbar ändern sie. Ein fester Wert
 * liess die Seite um ein paar Pixel scrollen oder schnitt unten etwas ab.
 *
 * Ohne JavaScript gilt der Rückfallwert im CSS (4 bzw. 5 rem), das ist nahe
 * genug. ResizeObserver meldet jede Änderung, auch beim Umschalten der Schrift.
 */
export function kopfhoeheVerdrahten() {
    const kopf = document.querySelector('header.sticky')

    if (!kopf || !('ResizeObserver' in window)) {
        return
    }

    const setzen = () => document.documentElement.style.setProperty('--kopfhoehe', `${kopf.offsetHeight}px`)

    setzen()
    new ResizeObserver(setzen).observe(kopf)
}
