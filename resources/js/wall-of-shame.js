/*
 * Wall of Shame (KEV-157).
 *
 * Jeder Klick auf den Papierstapel zeigt den nächsten Satz auf dem Zettel und
 * setzt einen Zählstrich an die Wand: vier senkrecht, der fünfte quer, wie im
 * Logo. Mit jedem Satz wird die Wand dunkler und die Striche kräftiger.
 *
 * Platz für die Striche: Die Größe richtet sich nach der Zahl aller Sätze,
 * nicht nach den gelesenen. So steht jeder Strich von Anfang an dort, wo er
 * bleibt, und am Ende ist die Wand voll, egal ob 50 oder 500 Sätze. Zettel
 * und Knopf bleiben frei. Bei jeder Größenänderung wird neu verteilt.
 *
 * Die Bündel tauchen verstreut über die ganze Wand auf, nicht Reihe für
 * Reihe: Die Wand füllt sich überall zugleich. Bündel und Striche sind
 * leicht schief, wie von Hand. Der Zufall hängt an festen Startwerten, damit
 * dasselbe Bild entsteht, egal wie oft neu gezeichnet wird.
 */

const SVG = 'http://www.w3.org/2000/svg'

// Kleiner Zufallsgenerator mit Startwert (mulberry32).
function zufall(startwert) {
    let a = startwert >>> 0
    return () => {
        a = (a + 0x6d2b79f5) >>> 0
        let t = a
        t = Math.imul(t ^ (t >>> 15), t | 1)
        t ^= t + Math.imul(t ^ (t >>> 7), t | 61)
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296
    }
}

function ueberschneidet(a, b) {
    return a.x < b.x + b.w && a.x + a.w > b.x && a.y < b.y + b.h && a.y + a.h > b.y
}

/**
 * Freie Felder für `anzahl` Fünferbündel auf einer Fläche, ohne die
 * gesperrten Rechtecke. Sucht die größte Feldgröße, bei der alle Platz haben.
 */
export function felderVerteilen(breite, hoehe, anzahl, gesperrt, rand = 8) {
    if (anzahl <= 0 || breite <= 0 || hoehe <= 0) return { felder: [], groesse: 0 }

    for (let groesse = Math.min(hoehe / 2, 120); groesse >= 6; groesse -= 1) {
        const fb = groesse * 1.15
        const spalten = Math.floor((breite - 2 * rand) / fb)
        const zeilen = Math.floor((hoehe - 2 * rand) / groesse)
        if (spalten * zeilen < anzahl) continue

        const versatz = (breite - spalten * fb) / 2
        const felder = []
        for (let z = 0; z < zeilen && felder.length < anzahl; z++) {
            for (let s = 0; s < spalten && felder.length < anzahl; s++) {
                const feld = { x: versatz + s * fb, y: rand + z * groesse, w: fb, h: groesse }
                if (!gesperrt.some((r) => ueberschneidet(feld, r))) felder.push(feld)
            }
        }
        if (felder.length >= anzahl) return { felder: verstreut(felder, anzahl), groesse }
    }

    return { felder: [], groesse: 0 }
}

/**
 * `anzahl` Felder aus allen freien, in fester, gemischter Reihenfolge. Gibt es
 * mehr freie Felder als Bündel, verteilen sich die Bündel trotzdem über die
 * ganze Wand, statt oben gedrängt zu stehen.
 */
function verstreut(felder, anzahl) {
    const r = zufall(4711)
    const gemischt = [...felder]
    for (let i = gemischt.length - 1; i > 0; i--) {
        const j = Math.floor(r() * (i + 1))
        ;[gemischt[i], gemischt[j]] = [gemischt[j], gemischt[i]]
    }
    return gemischt.slice(0, anzahl)
}

/** Ein Strich: Nummer 0 bis 3 senkrecht, 4 quer über die vier. */
function strich(feld, nummer, gesamtNummer) {
    const r = zufall(gesamtNummer * 7919 + 17)
    const wackel = (staerke) => (r() - 0.5) * staerke
    const { x, y, w, h } = feld

    if (nummer < 4) {
        const sx = x + w * (0.2 + nummer * 0.17) + wackel(w * 0.05)
        return [sx + wackel(w * 0.06), y + h * 0.14 + wackel(h * 0.06), sx + wackel(w * 0.06), y + h * 0.86 + wackel(h * 0.06)]
    }

    return [x + w * 0.08 + wackel(w * 0.04), y + h * 0.72 + wackel(h * 0.06), x + w * 0.92 + wackel(w * 0.04), y + h * 0.3 + wackel(h * 0.06)]
}

export function wallOfShameVerdrahten() {
    document.querySelectorAll('[data-wall-of-shame]').forEach(verdrahten)
}

function verdrahten(bereich) {
    const wand = bereich.querySelector('[data-wand]')
    const saetze = [...bereich.querySelectorAll('[data-wand-liste] li[data-satz]')].map((li) => li.dataset.satz)
    if (!wand || saetze.length === 0) return

    const svg = wand.querySelector('[data-wand-striche]')
    const schatten = wand.querySelector('[data-wand-schatten]')
    const zettel = wand.querySelector('[data-wand-zettel]')
    const satz = wand.querySelector('[data-wand-satz]')
    const zaehler = wand.querySelector('[data-wand-zaehler]')
    const stapel = wand.querySelector('[data-wand-stapel]')
    const knopf = wand.querySelector('[data-wand-knopf]')
    const schluss = bereich.querySelector('[data-wand-schluss]')?.textContent.trim() ?? ''
    const text = bereich.dataset
    const gesamt = saetze.length
    // 0 = noch nichts gelesen, 1 bis gesamt = Satz Nummer schritt,
    // gesamt + 1 = Schluss. Gezählt wird höchstens bis gesamt.
    let schritt = 0
    let gelesen = 0

    wand.hidden = false

    // Ohne Bewegung, wenn das System es so will.
    const ruhig = window.matchMedia('(prefers-reduced-motion: reduce)').matches

    function zeichnen(neu = false) {
        const flaeche = wand.getBoundingClientRect()
        const relativ = (el) => {
            const b = el.getBoundingClientRect()
            return { x: b.left - flaeche.left - 6, y: b.top - flaeche.top - 6, w: b.width + 12, h: b.height + 12 }
        }

        const { felder, groesse } = felderVerteilen(
            flaeche.width, flaeche.height, Math.ceil(gesamt / 5), [relativ(zettel), relativ(stapel)],
        )

        // Je weiter gelesen, desto dunkler und kräftiger.
        const anteil = gelesen / gesamt
        const deckkraft = 0.55 + 0.4 * anteil
        const breite = Math.max(1.5, groesse * (0.055 + 0.03 * anteil))

        svg.replaceChildren()
        svg.setAttribute('viewBox', `0 0 ${flaeche.width} ${flaeche.height}`)

        // Jedes Bündel leicht verschoben und gedreht, wie von Hand.
        const buendel = felder.map((feld, nr) => {
            const r = zufall(nr * 104729 + 3)
            const g = document.createElementNS(SVG, 'g')
            const dx = (r() - 0.5) * feld.w * 0.2
            const dy = (r() - 0.5) * feld.h * 0.2
            const winkel = (r() - 0.5) * 16
            g.setAttribute('transform', `translate(${dx} ${dy}) rotate(${winkel} ${feld.x + feld.w / 2} ${feld.y + feld.h / 2})`)
            return g
        })

        for (let i = 0; i < gelesen && i < felder.length * 5; i++) {
            const [x1, y1, x2, y2] = strich(felder[Math.floor(i / 5)], i % 5, i)
            const linie = document.createElementNS(SVG, 'line')
            linie.setAttribute('x1', x1)
            linie.setAttribute('y1', y1)
            linie.setAttribute('x2', x2)
            linie.setAttribute('y2', y2)
            linie.setAttribute('stroke', `rgba(24, 16, 12, ${deckkraft})`)
            linie.setAttribute('stroke-width', breite)
            linie.setAttribute('stroke-linecap', 'round')
            if (neu && !ruhig && i === gelesen - 1) {
                linie.setAttribute('pathLength', '1')
                linie.classList.add('wand-strich-neu')
            }
            buendel[Math.floor(i / 5)].append(linie)
        }

        svg.append(...buendel.filter((g) => g.childNodes.length > 0))

        schatten.style.opacity = String(0.5 * Math.pow(anteil, 1.3))
        zettel.style.rotate = `${-1 - 2.5 * anteil}deg`
    }

    function anzeigen() {
        zaehler.textContent = text.textZaehler.replace(':nr', gelesen).replace(':gesamt', gesamt)

        if (schritt <= gesamt) {
            satz.textContent = `„${saetze[schritt - 1]}“`
            knopf.textContent = schritt === gesamt ? text.textSchluss : text.textWeiter
            return
        }

        // Schluss: Wie viele es waren, darunter Taddis Satz in Fett.
        satz.replaceChildren(document.createTextNode(text.textEnde.replace(':gesamt', gesamt)))
        if (schluss) {
            const fett = document.createElement('strong')
            fett.className = 'mt-2 block font-semibold'
            fett.textContent = schluss
            satz.append(fett)
        }
        knopf.textContent = text.textNeu
    }

    stapel.addEventListener('click', () => {
        schritt = schritt > gesamt ? 1 : schritt + 1
        const vorher = gelesen
        gelesen = Math.min(schritt, gesamt)
        anzeigen()
        zeichnen(gelesen > vorher)
    })

    if ('ResizeObserver' in window) {
        new ResizeObserver(() => zeichnen()).observe(wand)
    } else {
        window.addEventListener('resize', () => zeichnen())
    }
}
