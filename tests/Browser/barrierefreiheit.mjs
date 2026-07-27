/*
 * Automatisierte WCAG-2.1-AA-Prüfung mit axe-core.
 *
 *   npm run test:a11y             (Server muss laufen, siehe bin/start)
 *   BASIS=http://localhost:8080 npm run test:a11y
 *
 * Warum das hier steht:
 * „Maximal barrierefrei“ ist die zentrale Zusage dieses Auftrags und zugleich
 * Risiko 4 im Projektplan. Ohne Messung ist sie nicht abnahmefähig — weder
 * gegenüber dem Verein noch gegenüber einer Prüfstelle.
 *
 * Was das hier NICHT ist:
 * axe-core findet je nach Quelle 30–50 % der Verstösse. Es findet fehlende
 * Alternativtexte, kaputte Kontraste und falsche ARIA-Rollen. Es findet nicht,
 * ob ein Alternativtext etwas Sinnvolles sagt, ob die Reihenfolge logisch ist
 * oder ob die Sprache verständlich bleibt. Ein bestandener Lauf heisst
 * „keine maschinell findbaren Fehler“, nicht „barrierefrei“. Der manuelle
 * Durchgang und der Test mit einer echten Vorlesehilfe bleiben nötig.
 *
 * Geprüft wird in jeder freigeschalteten Sprache: Ein Kontrastfehler kann in
 * einer Übersetzung auftauchen, die es im Deutschen nicht gibt, und eine
 * fehlende lang-Auszeichnung fällt nur in der Fremdsprache auf.
 */

import { chromium } from 'playwright'
import { readFileSync } from 'node:fs'
import { createRequire } from 'node:module'

const BASIS = process.env.BASIS || 'http://localhost:8000'

const axeQuelle = readFileSync(
    createRequire(import.meta.url).resolve('axe-core/axe.min.js'),
    'utf8'
)

/*
 * Die Regelsätze, die WCAG 2.1 AA abbilden. „best-practice“ ist bewusst NICHT
 * dabei: Darin stecken Empfehlungen, die keine Norm sind, und ein Test, der
 * bei Geschmacksfragen rot wird, wird nach zwei Wochen ignoriert.
 */
const NORMEN = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']

/*
 * Repräsentative Seiten statt aller 24: je Bauart eine.
 * Läuft in unter einer Minute und deckt jeden Baustein-Typ mindestens einmal ab.
 */
const SEITEN = [
    ['Startseite', '/'],
    ['Inhaltsseite', '/verein'],
    ['Seite mit Kontaktformular', '/anfragen'],
    ['Vorstand und Team', '/ueber-uns-vorstand-und-team'],
    ['Gruppen', '/selbsthilfegruppen'],
    ['Blog-Übersicht', '/aktuelles'],
    ['Veranstaltungen', '/veranstaltungen'],
    ['Rechtstext', '/impressum'],
    ['Fehlerseite', '/diese-seite-gibt-es-nicht'],
]

/*
 * Zustände, die man nur im Browser erreicht. Genau dort sind Verstösse
 * wahrscheinlich: Ein zugeklapptes Panel prüft axe nicht, ein aufgeklapptes
 * schon — und der Kontrastmodus ändert jede Farbe der Seite.
 */
const ZUSTAENDE = [
    {
        name: 'Einstellungs-Panel offen',
        vorbereiten: async (seite) => {
            await seite.locator('button[aria-controls="a11y-panel"]').click()
            await seite.locator('#a11y-panel').waitFor({ state: 'visible' })
        },
    },
    {
        name: 'Hoher Kontrast',
        vorbereiten: async (seite) => {
            await seite.locator('button[aria-controls="a11y-panel"]').click()
            await seite.locator('[data-a11y-setzen="kontrast"][data-a11y-wert="hoch"]').click()
            await seite.keyboard.press('Escape')
        },
    },
    {
        name: 'Dunkelmodus',
        vorbereiten: async (seite) => {
            await seite.locator('button[aria-controls="a11y-panel"]').click()
            await seite.locator('[data-a11y-setzen="kontrast"][data-a11y-wert="dunkel"]').click()
            await seite.keyboard.press('Escape')
        },
    },
    {
        name: 'Größte Schrift',
        vorbereiten: async (seite) => {
            await seite.locator('button[aria-controls="a11y-panel"]').click()
            await seite.locator('[data-a11y-setzen="schrift"][data-a11y-wert="3"]').click()
            await seite.keyboard.press('Escape')
        },
    },
]

const browser = await chromium.launch()
let verstoesse = 0
let geprueft = 0

/** Welche Sprachen sind freigeschaltet? Aus der Seite selbst gelesen. */
async function sprachen() {
    const seite = await browser.newPage()
    await seite.goto(BASIS + '/', { waitUntil: 'networkidle' })
    const codes = await seite.$$eval(
        'header nav[aria-label] a[hreflang]',
        (a) => [...new Set(a.map((e) => e.getAttribute('hreflang')))]
    ).catch(() => [])
    await seite.close()

    return codes.length ? codes : ['de']
}

/** Präfix einer Sprache: die Standardsprache hat keines. */
function pfad(code, standard, seitenpfad) {
    if (code === standard) return seitenpfad

    return seitenpfad === '/' ? `/${code}` : `/${code}${seitenpfad}`
}

async function pruefen(titel, url, vorbereiten = null, breite = 1400) {
    const kontext = await browser.newContext({ viewport: { width: breite, height: 900 } })
    const seite = await kontext.newPage()

    // 404 ist für die Fehlerseite der erwartete Status — kein Abbruchgrund.
    await seite.goto(url, { waitUntil: 'networkidle' })

    if (vorbereiten) {
        await vorbereiten(seite)
        await seite.waitForTimeout(150)
    }

    await seite.addScriptTag({ content: axeQuelle })

    const ergebnis = await seite.evaluate(
        (normen) => window.axe.run(document, { runOnly: { type: 'tag', values: normen } }),
        NORMEN
    )

    geprueft++

    if (ergebnis.violations.length === 0) {
        console.log(`  ✓ ${titel}`)
    } else {
        for (const v of ergebnis.violations) {
            verstoesse++
            const stellen = v.nodes.slice(0, 3).map((n) => n.target.join(' ')).join(' | ')
            const weitere = v.nodes.length > 3 ? ` (+${v.nodes.length - 3} weitere)` : ''
            console.log(`  ✗ ${titel}`)
            console.log(`      ${v.id} [${v.impact}] — ${v.help}`)
            console.log(`      ${stellen}${weitere}`)
            console.log(`      ${v.helpUrl}`)
        }
    }

    await kontext.close()
}

const codes = await sprachen()
const standard = codes[0]

console.log(`\naxe-core — WCAG 2.1 AA`)
console.log(`Sprachen: ${codes.join(', ')} (Standard: ${standard})`)

for (const code of codes) {
    console.log(`\nSeiten [${code}]`)
    for (const [titel, p] of SEITEN) {
        await pruefen(`${titel} (${code})`, BASIS + pfad(code, standard, p))
    }
}

// Zustände nur in der Standardsprache: Sie hängen an der Toolbar und an CSS,
// nicht am Text. Alle Sprachen zu wiederholen kostet Zeit ohne Erkenntnis.
console.log('\nZustände der Darstellungs-Einstellungen')
for (const zustand of ZUSTAENDE) {
    await pruefen(zustand.name, BASIS + '/verein', zustand.vorbereiten)
}

console.log('\nMobil (390 px)')
for (const [titel, p] of [['Startseite', '/'], ['Inhaltsseite', '/verein']]) {
    await pruefen(`${titel} mobil`, BASIS + p, null, 390)
}

// 320 px ist die Untergrenze aus WCAG 1.4.10: Ab hier darf nicht waagerecht
// gescrollt werden müssen. axe prüft das nicht, deshalb hier von Hand.
console.log('\nReflow (320 px, WCAG 1.4.10)')
{
    const kontext = await browser.newContext({ viewport: { width: 320, height: 800 } })
    const seite = await kontext.newPage()

    for (const [titel, p] of [['Startseite', '/'], ['Inhaltsseite', '/verein'], ['Anfrage', '/anfragen']]) {
        await seite.goto(BASIS + p, { waitUntil: 'networkidle' })
        const ueberlauf = await seite.evaluate(() => {
            const d = document.documentElement

            return d.scrollWidth - d.clientWidth
        })
        geprueft++

        if (ueberlauf <= 0) {
            console.log(`  ✓ ${titel}`)
        } else {
            verstoesse++
            console.log(`  ✗ ${titel} — ${ueberlauf} px waagerechter Überlauf`)
        }
    }

    await kontext.close()
}

await browser.close()

console.log(`\n${geprueft} Durchläufe.`)
console.log(
    verstoesse === 0
        ? 'Keine maschinell findbaren Verstösse. Der manuelle Durchgang bleibt nötig.\n'
        : `${verstoesse} Verstoss/Verstösse.\n`
)
process.exit(verstoesse === 0 ? 0 : 1)
