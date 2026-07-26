/*
 * Barrierefreiheits-Einstellungen.
 *
 * Ersetzt das OneTap-WordPress-Plugin der Altseite. Kuratiert statt vollständig:
 * die Funktionen mit echtem Nutzen, ohne die ~200 KB Inline-Ballast pro Seite.
 *
 * Speicherung in localStorage. Das ist technisch notwendige Funktionalität auf
 * ausdrücklichen Nutzerwunsch — kein Consent nötig, gehört aber in die
 * Datenschutzerklärung.
 *
 * Die Werte werden ausschließlich als Attribute/Klassen auf <html> gesetzt.
 * Kein Element wird einzeln angefasst — das hält die Sache vorhersehbar und
 * macht sie über CSS-Custom-Properties skalierbar.
 */

export const SPEICHER_SCHLUESSEL = 'ke-a11y'

export const EINSTELLUNGEN = {
    schrift: {
        label: 'Schriftgröße',
        typ: 'stufen',
        stufen: [
            { wert: 0, label: 'Standard' },
            { wert: 1, label: 'Groß' },
            { wert: 2, label: 'Größer' },
            { wert: 3, label: 'Sehr groß' },
        ],
    },
    zeilen: {
        label: 'Zeilenabstand',
        typ: 'stufen',
        stufen: [
            { wert: 0, label: 'Standard' },
            { wert: 1, label: 'Weit' },
            { wert: 2, label: 'Sehr weit' },
        ],
    },
    zeichen: {
        label: 'Buchstabenabstand',
        typ: 'stufen',
        stufen: [
            { wert: 0, label: 'Standard' },
            { wert: 1, label: 'Weit' },
            { wert: 2, label: 'Sehr weit' },
        ],
    },
    kontrast: {
        label: 'Kontrast & Farben',
        typ: 'auswahl',
        optionen: [
            { wert: '', label: 'Standard' },
            { wert: 'dunkel', label: 'Dunkel' },
            { wert: 'hoch', label: 'Hoher Kontrast' },
            { wert: 'monochrom', label: 'Graustufen' },
        ],
    },
    lesbar: { label: 'Gut lesbare Schrift', typ: 'schalter' },
    dyslexie: { label: 'Schrift für Legasthenie', typ: 'schalter' },
    leselinie: { label: 'Leselinie', typ: 'schalter' },
    links: { label: 'Links hervorheben', typ: 'schalter' },
    cursor: { label: 'Großer Mauszeiger', typ: 'schalter' },
    ruhe: { label: 'Bewegung stoppen', typ: 'schalter' },
    bilder: { label: 'Bilder ausblenden', typ: 'schalter' },
}

/** Gespeicherte Werte lesen. Defekte Daten dürfen die Seite nicht mitreißen. */
export function lesen() {
    try {
        return JSON.parse(localStorage.getItem(SPEICHER_SCHLUESSEL)) || {}
    } catch {
        return {}
    }
}

export function speichern(werte) {
    try {
        localStorage.setItem(SPEICHER_SCHLUESSEL, JSON.stringify(werte))
    } catch {
        /* Privater Modus o.ä. — dann gilt die Einstellung eben nur für diese Sitzung. */
    }
}

/**
 * Werte auf <html> übertragen.
 * Wird an zwei Stellen aufgerufen: hier bei jeder Änderung und als Inline-Kopie
 * im <head>, damit beim Laden nichts aufblitzt.
 */
export function anwenden(werte) {
    const el = document.documentElement

    // Schriftgröße skaliert über die rem-Basis -> alles skaliert mit.
    const skalen = [1, 1.15, 1.3, 1.5]
    el.style.setProperty('--a11y-font-scale', skalen[werte.schrift || 0])
    el.style.setProperty('--a11y-line-height', [1.7, 2, 2.3][werte.zeilen || 0])
    el.style.setProperty('--a11y-letter-spacing', ['0em', '0.05em', '0.1em'][werte.zeichen || 0])

    el.dataset.kontrast = werte.kontrast || ''

    for (const name of ['lesbar', 'dyslexie', 'leselinie', 'links', 'cursor', 'ruhe', 'bilder']) {
        el.classList.toggle(`a11y-${name}`, !!werte[name])
    }
}
