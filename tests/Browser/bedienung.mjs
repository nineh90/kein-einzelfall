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
    //
    // Erst an den oberen Rand scrollen: Ohne JavaScript steht die
    // Trigger-Warnung als Block über dem Kopf, und Playwright holt den Knopf
    // sonst an den *unteren* Rand — genau dorthin, wo die fixe Mobil-Leiste
    // liegt und den Klick abfängt. `instant`, weil die Seite sonst weich
    // scrollt und Playwright den Knopf währenddessen für „nicht stabil“ hält.
    const burger = mobil.locator('details', { has: menue }).locator('summary').first()
    await burger.evaluate((el) => el.scrollIntoView({ block: 'start', behavior: 'instant' }))
    await burger.click()
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

    // „Nicht mehr anzeigen“ ist ein Kontrollkästchen: ankreuzen, dann schliessen.
    await wiederkehr.locator('#trigger-warnung [data-trigger-nie]').check()
    await wiederkehr.locator('#trigger-warnung [data-trigger-weiter]').click()
    await wiederkehr.reload({ waitUntil: 'networkidle' })
    pruefe('„nicht mehr anzeigen“ hält dauerhaft',
        !(await wiederkehr.locator('#trigger-warnung').isVisible()))

    // Ankreuzen und dann ESC ist dieselbe Entscheidung. Sie hing früher am
    // Klick und wäre auf diesem Weg verlorengegangen.
    const perEsc = await oeffnen({ trigger: true })
    await perEsc.locator('#trigger-warnung [data-trigger-nie]').check()
    await perEsc.keyboard.press('Escape')
    await perEsc.reload({ waitUntil: 'networkidle' })
    pruefe('gilt auch, wenn der Dialog per Escape geschlossen wird',
        !(await perEsc.locator('#trigger-warnung').isVisible()))
}

console.log('\nTrigger-Warnung: Aussehen der Bedienelemente')
{
    /*
     * Kevins Rückmeldung zur ersten Fassung: „Die Buttons gehen gar nicht.“
     * Sie waren unterschiedlich groß und einer per ms-auto an den Rand
     * geschoben. Jetzt kommen beide aus derselben Knopf-Komponente — dieser
     * Test hält fest, dass sie nicht wieder auseinanderlaufen.
     */
    const seite = await oeffnen({ trigger: true })
    const dialog = seite.locator('#trigger-warnung')

    const weiter = await dialog.locator('[data-trigger-weiter]').boundingBox()
    const exit = await dialog.locator('a[data-notausgang]').boundingBox()

    pruefe('beide Knöpfe sind gleich hoch',
        Math.abs(weiter.height - exit.height) < 1,
        `weiterlesen ${Math.round(weiter.height)} px, Notausgang ${Math.round(exit.height)} px`)

    pruefe('beide Knöpfe stehen auf einer Linie',
        Math.abs(weiter.y - exit.y) < 1,
        'kein ms-auto, kein Umbruch')

    // Auf dem Handy über die volle Breite: Wer in einer angespannten Lage
    // tippt, trifft eine ganze Zeile zuverlässiger als eine halbe.
    const mobil = await oeffnen({ trigger: true, breite: 390 })
    const mDialog = mobil.locator('#trigger-warnung')
    const mWeiter = await mDialog.locator('[data-trigger-weiter]').boundingBox()
    const mExit = await mDialog.locator('a[data-notausgang]').boundingBox()

    pruefe('mobil sind beide gleich breit',
        Math.abs(mWeiter.width - mExit.width) < 1,
        `${Math.round(mWeiter.width)} px vs. ${Math.round(mExit.width)} px`)

    pruefe('mobil stehen sie untereinander', mExit.y > mWeiter.y + mWeiter.height - 1)
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
    pruefe('der Knopf zum Wegklicken bleibt verborgen',
        !(await dialog.locator('[data-trigger-weiter]').isVisible()))

    pruefe('das Kästchen „nicht mehr anzeigen“ bleibt verborgen',
        !(await dialog.locator('[data-trigger-nie]').isVisible()))

    pruefe('stattdessen steht da, warum',
        await dialog.locator('[data-trigger-ohne-js]').isVisible())
}

// --- Gespeicherte Einstellungen zurücksetzen --------------------------------
// Der Weg zurück. Wer „Hinweis nicht mehr anzeigen“ gewählt hat, muss das
// widerrufen können, ohne die Browser-Einstellungen zu durchsuchen — auf einem
// geteilten Gerät ist das kein theoretisches Problem.

console.log('\nGespeicherte Einstellungen')
{
    // Erst etwas speichern: Hinweis dauerhaft abbestellen.
    const erst = await oeffnen({ trigger: true })
    await erst.locator('#trigger-warnung [data-trigger-nie]').check()
    await erst.locator('#trigger-warnung [data-trigger-weiter]').click()

    await erst.goto(BASIS + '/barrierefreiheit', { waitUntil: 'networkidle' })
    const zeile = erst.locator('[data-speicher-eintrag="trigger"]')

    pruefe('die Übersicht zeigt den gespeicherten Zustand',
        (await zeile.locator('[data-speicher-status]').textContent()).trim().length > 0)

    pruefe('der Zurücksetzen-Knopf ist bedienbar, solange etwas gespeichert ist',
        await zeile.locator('[data-speicher-loeschen]').isEnabled())

    await zeile.locator('[data-speicher-loeschen]').click()

    pruefe('nach dem Zurücksetzen ist der Speicher leer',
        await erst.evaluate(() => localStorage.getItem('ke.trigger.aus') === null))

    pruefe('der Knopf schaltet sich danach ab',
        !(await zeile.locator('[data-speicher-loeschen]').isEnabled()),
        'sonst drückt man ins Leere')

    // Und der Hinweis ist wirklich zurück — das ist der Punkt der Übung.
    await erst.goto(BASIS + '/', { waitUntil: 'networkidle' })
    pruefe('der Hinweis erscheint danach wieder',
        await erst.locator('#trigger-warnung').isVisible())
}

console.log('\n„Alles zurücksetzen“ in der Darstellungs-Toolbar')
{
    /*
     * Der Knopf heisst „Alles zurücksetzen“ und räumte lange nur die
     * Darstellung ab. Mit der Trigger-Warnung kam ein zweiter gespeicherter
     * Wert dazu, und die Beschriftung wurde stillschweigend falsch.
     */
    const seite = await oeffnen({ trigger: true })
    await seite.locator('#trigger-warnung [data-trigger-nie]').check()
    await seite.locator('#trigger-warnung [data-trigger-weiter]').click()

    await seite.locator('button[aria-controls="a11y-panel"]').click()
    await seite.locator('[data-a11y-setzen="kontrast"][data-a11y-wert="hoch"]').click()
    await seite.locator('[data-a11y-zuruecksetzen]').click()

    pruefe('räumt die Darstellung ab',
        await seite.evaluate(() => document.documentElement.dataset.kontrast) === '')

    pruefe('räumt auch den Hinweis ab',
        await seite.evaluate(() => localStorage.getItem('ke.trigger.aus') === null),
        '„Alles“ muss alles heissen')
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
