/*
 * Bedienbarkeits-Test in einem echten Browser.
 *
 *   npm run test:browser          (Server muss laufen, siehe bin/start)
 *   BASIS=http://localhost:8080 npm run test:browser
 *
 * Einmalig vorher:  npx playwright install chromium
 *
 * Warum zusätzlich zu `php artisan test`:
 * PHPUnit prüft das ausgelieferte HTML. Ob ein Knopf im Browser auch etwas
 * bewirkt, sieht es prinzipiell nicht. Genau daran ist die Toolbar wochenlang
 * gescheitert — Alpine wertete seine Attribute über new Function() aus, unsere
 * Content-Security-Policy verbietet das, und der Knopf war lautlos tot. Im HTML
 * war nichts Auffälliges zu sehen, alle Tests waren grün.
 *
 * Deshalb wird hier auch die Konsole mitgelesen: Eine blockierte Auswertung
 * meldet der Browser nur dort.
 */

import { chromium } from 'playwright'

const BASIS = process.env.BASIS || 'http://localhost:8000'

let fehlgeschlagen = 0

function pruefe(name, bedingung, zusatz = '') {
    if (bedingung) {
        console.log(`  ✓ ${name}`)
    } else {
        console.log(`  ✗ ${name}${zusatz ? ' — ' + zusatz : ''}`)
        fehlgeschlagen++
    }
}

const browser = await chromium.launch()
const meldungen = []

/** Seite öffnen und dabei jede Browser-Meldung mitschneiden. */
async function oeffnen({ js = true, breite = 1400, pfad = '/' } = {}) {
    const kontext = await browser.newContext({
        javaScriptEnabled: js,
        viewport: { width: breite, height: 900 },
    })
    const seite = await kontext.newPage()
    seite.on('pageerror', (e) => meldungen.push(`[js=${js} ${breite}px] ${e.message}`))
    seite.on('console', (m) => m.type() === 'error' && meldungen.push(`[js=${js} ${breite}px] ${m.text()}`))

    await seite.goto(BASIS + pfad, { waitUntil: js ? 'networkidle' : 'load' })

    return seite
}

// --- Darstellungs-Toolbar ---------------------------------------------------

console.log('\nDarstellungs-Toolbar (Desktop)')
{
    const seite = await oeffnen()
    const knopf = seite.locator('button[aria-controls="a11y-panel"]')
    const panel = seite.locator('#a11y-panel')

    pruefe('Panel startet zugeklappt', !(await panel.isVisible()))

    await knopf.click()
    pruefe('Knopf öffnet das Panel', await panel.isVisible(),
        'genau der Fehler, den die CSP ausgelöst hat')
    pruefe('aria-expanded wird mitgeführt', await knopf.getAttribute('aria-expanded') === 'true')

    await seite.locator('[data-a11y-setzen="kontrast"][data-a11y-wert="hoch"]').click()
    pruefe('Kontrastmodus greift',
        await seite.evaluate(() => document.documentElement.dataset.kontrast) === 'hoch')

    await seite.locator('[data-a11y-setzen="schrift"][data-a11y-wert="3"]').click()
    pruefe('Schriftgröße greift',
        await seite.evaluate(() => document.documentElement.style.getPropertyValue('--a11y-font-scale')) === '1.5')

    await seite.locator('[data-a11y-umschalten="leselinie"]').click()
    pruefe('Schalter greift',
        await seite.evaluate(() => document.documentElement.classList.contains('a11y-leselinie')))

    pruefe('Zähler am Knopf stimmt', await seite.locator('[data-a11y-zaehler]').textContent() === '3')

    // Der eigentliche Zweck der Einstellungen: Sie müssen bleiben.
    await seite.goto(BASIS + '/wissen', { waitUntil: 'networkidle' })
    pruefe('überlebt den Seitenwechsel',
        await seite.evaluate(() => document.documentElement.dataset.kontrast) === 'hoch')

    await seite.locator('button[aria-controls="a11y-panel"]').click()
    await seite.locator('[data-a11y-zuruecksetzen]').click()
    pruefe('Zurücksetzen räumt alles ab',
        await seite.evaluate(() => document.documentElement.dataset.kontrast) === ''
        && !(await seite.evaluate(() => document.documentElement.classList.contains('a11y-leselinie'))))
}

// --- Nur mit der Tastatur ---------------------------------------------------

console.log('\nBedienung ohne Maus')
{
    const seite = await oeffnen()

    await seite.keyboard.press('Tab')
    pruefe('erster Tab-Stopp ist der Sprunglink',
        (await seite.evaluate(() => document.activeElement.textContent)).trim() === 'Zum Inhalt springen')

    let erreicht = false
    for (let i = 0; i < 25 && !erreicht; i++) {
        await seite.keyboard.press('Tab')
        erreicht = await seite.evaluate(() => document.activeElement.getAttribute('aria-controls') === 'a11y-panel')
    }
    pruefe('Toolbar-Knopf ist per Tab erreichbar', erreicht)

    await seite.keyboard.press('Enter')
    pruefe('Enter öffnet das Panel', await seite.locator('#a11y-panel').isVisible())

    await seite.keyboard.press('Escape')
    pruefe('Escape schliesst wieder', !(await seite.locator('#a11y-panel').isVisible()))
}

// --- Ohne JavaScript --------------------------------------------------------
// Die Altseite erzeugt Navigation und Notausgang per JavaScript. Ohne JS gibt
// es dort beides nicht. Das ist der Vergleichsmassstab.

console.log('\nOhne JavaScript')
{
    const mobil = await oeffnen({ js: false, breite: 390 })
    const menue = mobil.locator('nav[aria-label="Hauptnavigation (mobil)"]')

    pruefe('Mobil-Menü startet zugeklappt', !(await menue.isVisible()))
    await mobil.locator('summary').first().click()
    pruefe('Mobil-Menü lässt sich öffnen', await menue.isVisible(),
        'natives <details> — darf nicht an JavaScript hängen')

    const ausgaenge = mobil.locator('a[data-notausgang]')
    let sichtbar = 0
    for (let i = 0; i < await ausgaenge.count(); i++) {
        if (await ausgaenge.nth(i).isVisible()) sichtbar++
    }
    pruefe('Notausgang ist erreichbar', sichtbar > 0)

    const desktop = await oeffnen({ js: false, breite: 1400 })
    pruefe('Hauptnavigation steht im HTML',
        await desktop.locator('nav[aria-label="Hauptnavigation"]').isVisible())
    pruefe('Mobil-Menü bleibt auf dem Desktop verborgen',
        !(await desktop.locator('nav[aria-label="Hauptnavigation (mobil)"]').isVisible()))
}

// --- Auswertung -------------------------------------------------------------

console.log('\nBrowser-Meldungen')
if (meldungen.length === 0) {
    console.log('  ✓ keine')
} else {
    // Hierunter fiel der blockierte new-Function-Aufruf. Eine Meldung in der
    // Konsole ist genauso ein Fehler wie ein fehlgeschlagener Klick.
    for (const m of meldungen) console.log(`  ✗ ${m}`)
    fehlgeschlagen += meldungen.length
}

await browser.close()

console.log(fehlgeschlagen === 0 ? '\nAlles in Ordnung.\n' : `\n${fehlgeschlagen} Punkt(e) offen.\n`)
process.exit(fehlgeschlagen === 0 ? 0 : 1)
