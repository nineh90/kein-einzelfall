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

/**
 * Seite öffnen und dabei jede Browser-Meldung mitschneiden.
 *
 * `trigger: false` bestellt die vorgeschaltete Trigger-Warnung vorab ab —
 * so, wie es jemand tut, der sie einmal weggeklickt hat. Das ist für fast
 * alle Prüfungen hier der richtige Ausgangszustand: Der Hinweis ist ein
 * modaler Dialog, hält den Fokus fest und läge sonst vor jedem Knopf, um den
 * es in diesem Test geht. Die Warnung selbst wird weiter unten eigens geprüft.
 */
async function oeffnen({ js = true, breite = 1400, pfad = '/', trigger = false } = {}) {
    const kontext = await browser.newContext({
        javaScriptEnabled: js,
        viewport: { width: breite, height: 900 },
    })

    if (!trigger && js) {
        await kontext.addInitScript(() => localStorage.setItem('ke.trigger.aus', '1'))
    }

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
    // Gezielt das <details> des Mobilmenüs, nicht einfach das erste <summary>:
    // seit dem Weltkugel-Umschalter gibt es weiter oben im Kopf einen zweiten
    // Aufklapper (mobil ausgeblendet), sonst klickte der Test daneben.
    await mobil.locator('details', { has: menue }).locator('summary').first().click()
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

// --- Trigger-Warnung --------------------------------------------------------
// Ausdrücklicher Wunsch des Vereins: ein vorgeschalteter Hinweis, den man
// wegklicken kann (kommt beim nächsten Besuch wieder) oder dauerhaft abbestellt.
// Der Unterschied zwischen "diesmal" und "nie wieder" ist der ganze Punkt —
// deshalb wird er hier auch wirklich durchgespielt und nicht nur der Klick.

console.log('\nTrigger-Warnung')
{
    const seite = await oeffnen({ trigger: true })
    const dialog = seite.locator('#trigger-warnung')

    pruefe('Warnung erscheint beim ersten Besuch', await dialog.isVisible())

    pruefe('ist ein echter modaler Dialog',
        await seite.evaluate(() => document.getElementById('trigger-warnung')?.matches(':modal')),
        'ohne showModal() gäbe es weder Fokusfalle noch abgedunkelten Hintergrund')

    // Der Fokus muss im Dialog liegen. Sonst tabbte man hinter dem Hinweis
    // durch die Seite, die man noch gar nicht sehen sollte.
    await seite.keyboard.press('Tab')
    pruefe('der Fokus bleibt im Dialog',
        await seite.evaluate(() => document.getElementById('trigger-warnung')?.contains(document.activeElement)))

    pruefe('der Notausgang steht im Dialog',
        await dialog.locator('a[data-notausgang]').isVisible())

    await dialog.locator('[data-trigger-weiter]').click()
    pruefe('„weiterlesen“ schliesst den Hinweis', !(await dialog.isVisible()))

    // Weggeklickt heisst: für diesen Besuch erledigt.
    await seite.reload({ waitUntil: 'networkidle' })
    pruefe('bleibt im selben Besuch weg',
        !(await seite.locator('#trigger-warnung').isVisible()))

    // … aber nur für diesen. Ein neuer Kontext ist ein neuer Besuch.
    const wiederkehr = await oeffnen({ trigger: true })
    pruefe('kommt beim nächsten Besuch wieder',
        await wiederkehr.locator('#trigger-warnung').isVisible(),
        'genau der Unterschied, den der Verein gefordert hat')

    await wiederkehr.locator('#trigger-warnung [data-trigger-nie]').click()
    await wiederkehr.reload({ waitUntil: 'networkidle' })
    pruefe('„nicht mehr anzeigen“ hält dauerhaft',
        !(await wiederkehr.locator('#trigger-warnung').isVisible()))
}

console.log('\nTrigger-Warnung ohne JavaScript')
{
    // Der wichtigste Fall überhaupt: Ein Overlay, das erst JavaScript aufbaut,
    // gibt bei jedem Skriptfehler den Inhalt ungewarnt frei.
    const seite = await oeffnen({ js: false, trigger: true })
    const dialog = seite.locator('#trigger-warnung')

    pruefe('Warnung ist trotzdem sichtbar', await dialog.isVisible())

    pruefe('der Notausgang funktioniert trotzdem',
        await dialog.locator('a[data-notausgang]').isVisible())

    // Sie könnten nichts speichern und täten auf Druck nichts.
    pruefe('die Knöpfe zum Wegklicken bleiben verborgen',
        !(await dialog.locator('[data-trigger-weiter]').isVisible()))

    pruefe('stattdessen steht da, warum',
        await dialog.locator('[data-trigger-ohne-js]').isVisible())
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
