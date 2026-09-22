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

// --- Spendenhinweis ---------------------------------------------------------

console.log('\nSpendenhinweis für wiederkehrende Besucherinnen')
{
    /*
     * Der Kasten steht auf jeder erlaubten Seite im HTML, aber versteckt.
     * Erst der fünfte Aufruf holt ihn hervor; „Jetzt nicht“ bringt Ruhe.
     * Die Schwelle liest der Test vom Element ab, nicht aus der Config —
     * geprüft wird, was die Seite tatsächlich verspricht.
     */
    const seite = await oeffnen({ pfad: '/verein' })
    const kasten = seite.locator('#spendenhinweis')
    const ab = parseInt(await kasten.getAttribute('data-ab'), 10)

    pruefe('ist beim ersten Besuch nicht zu sehen', !(await kasten.isVisible()))

    for (let i = 2; i < ab; i++) {
        await seite.goto(BASIS + '/verein', { waitUntil: 'networkidle' })
    }
    pruefe(`bleibt bis zum ${ab - 1}. Aufruf weg`, !(await kasten.isVisible()))

    await seite.goto(BASIS + '/verein', { waitUntil: 'networkidle' })
    pruefe(`erscheint beim ${ab}. Aufruf`, await kasten.isVisible())

    pruefe('zieht den Fokus nicht an sich',
        await seite.evaluate(() => !document.getElementById('spendenhinweis').contains(document.activeElement)),
        'wer liest, liest weiter')

    // Der Kasten ist ein Landmark mit Namen — so findet ihn eine Vorlesehilfe,
    // ohne dass er sie unterbricht.
    pruefe('ist ein benannter Landmark',
        await seite.evaluate(() => {
            const k = document.getElementById('spendenhinweis')
            return k.tagName === 'ASIDE' && !!document.getElementById(k.getAttribute('aria-labelledby'))
        }))

    await seite.locator('[data-spendenhinweis-schliessen]').first().click()
    pruefe('verschwindet bei „Jetzt nicht“', !(await kasten.isVisible()))

    await seite.goto(BASIS + '/verein', { waitUntil: 'networkidle' })
    pruefe('bleibt danach weg', !(await kasten.isVisible()))

    // Auf der Spendenseite gibt es ihn gar nicht — auch nicht mit Zähler.
    await seite.evaluate(() => { localStorage.removeItem('ke.spenden.ruhe'); localStorage.setItem('ke.spenden.aufrufe', '99') })
    await seite.goto(BASIS + '/spenden', { waitUntil: 'networkidle' })
    pruefe('steht nicht auf der Spendenseite', (await seite.locator('#spendenhinweis').count()) === 0)

    await seite.context().close()
}

console.log('\nSpendenhinweis wartet auf die Trigger-Warnung')
{
    // Wer die Warnung noch liest, wird nicht daneben um Geld gebeten.
    const kontext = await browser.newContext({ viewport: { width: 1400, height: 900 } })
    await kontext.addInitScript(() => localStorage.setItem('ke.spenden.aufrufe', '99'))
    const seite = await kontext.newPage()
    seite.on('pageerror', (e) => meldungen.push(`[trigger+hinweis] ${e.message}`))
    await seite.goto(BASIS + '/verein', { waitUntil: 'networkidle' })

    pruefe('bleibt hinter dem offenen Dialog verborgen', !(await seite.locator('#spendenhinweis').isVisible()))

    await seite.locator('#trigger-warnung [data-trigger-weiter]').click()
    await seite.waitForTimeout(100)
    pruefe('kommt, sobald der Dialog zu ist', await seite.locator('#spendenhinweis').isVisible())

    await kontext.close()
}

// --- Vereinsname im Hinweisband ---------------------------------------------
//
// Der handschriftliche Schriftzug muss auf jeder Breite vollstaendig in den
// Kasten passen. Abgeschnitten waere er wieder nur „angedeutet" — und genau
// das war der Anlass fuer KEV-15.
//
// Warum als Browser-Test und nicht in PHP: Es ist eine reine Layout-Frage, die
// erst beim Rendern entsteht. Der Kasten hat `overflow-hidden`, ein Ueberlauf
// erzeugt also weder horizontalen Scroll noch einen axe-Verstoss — er faellt
// stillschweigend nur optisch auf. Beim ersten Entwurf lief der Name auf 320px
// um 68px heraus, ohne dass ein einziger Test rot wurde.
//
// Der Text ist im Panel pflegbar. Wer ihn verlaengert, soll es hier merken.

console.log('\nVereinsname im Hinweisband')
{
    for (const breite of [320, 390, 768, 1400]) {
        const seite = await oeffnen({ breite })

        const mass = await seite.evaluate(() => {
            const schrift = document.querySelector('[data-wasserzeichen]')
            if (! schrift) return null
            if (getComputedStyle(schrift).display === 'none') return 'ausgeblendet'

            const a = schrift.getBoundingClientRect()
            const kasten = schrift.parentElement.getBoundingClientRect()

            return { links: Math.round(a.left - kasten.left), rechts: Math.round(kasten.right - a.right) }
        })

        if (mass === null || mass === 'ausgeblendet') {
            pruefe(`${breite}px: Schriftzug vorhanden`, false, 'nicht gefunden')
            await seite.context().close()
            continue
        }

        pruefe(`${breite}px: bleibt im Kasten`, mass.rechts >= 4 && mass.links >= 0,
            `Luft links ${mass.links}px, rechts ${mass.rechts}px`)

        await seite.context().close()
    }
}

// --- Suche (KEV-23) ---------------------------------------------------------
//
// Geprueft wird, was ein PHP-Test nicht sehen kann: dass das Formular ohne
// JavaScript abschickt, dass es mit der Tastatur allein bedienbar ist und dass
// der Weg vom Kopf der Seite bis zum Treffer funktioniert.

console.log('\nSuche')
{
    /*
     * Ohne JavaScript — der wichtigste Fall. Wer mit Screenreader, altem Geraet
     * oder abgeschaltetem JS kommt, muss suchen koennen.
     *
     * Geprueft wird die Ergebnisseite und nicht der Klick auf „Suchen": Ohne JS
     * steht die Trigger-Warnung offen ueber der Seite und laesst sich nicht
     * wegklicken (das braucht JS, so ist sie gebaut). Das Absenden selbst ist
     * ohnehin natives Browserverhalten — was wir wissen muessen, ist, dass die
     * Zieladresse ohne eine Zeile JavaScript vollstaendige Treffer liefert.
     * Dass Enter im Feld abschickt, prueft der Tastatur-Fall darunter.
     */
    const ohne = await oeffnen({ js: false, pfad: '/suche?q=pflegegrad' })

    pruefe('liefert Treffer ohne JavaScript',
        (await ohne.locator('[data-treffer] li').count()) > 0)

    pruefe('Treffer sind echte Links',
        (await ohne.locator('[data-treffer] li a[href]').count()) > 0)

    pruefe('Suchfeld traegt die Anfrage weiter',
        (await ohne.locator('#suchfeld').inputValue()) === 'pflegegrad')

    await ohne.context().close()

    // Mit Tastatur allein: Feld anspringen, tippen, Enter.
    const seite = await oeffnen({ pfad: '/suche' })
    await seite.locator('#suchfeld').focus()
    await seite.keyboard.type('gdb')
    await seite.keyboard.press('Enter')

    // waitForURL und nicht waitForLoadState: Die Navigation startet erst nach
    // dem Tastendruck. „networkidle" waere in dem Moment schon erfuellt — vom
    // alten Seitenzustand — und der Test liefe gegen die vorige Adresse.
    await seite.waitForURL(/[?&]q=/, { timeout: 5000 }).catch(() => {})

    pruefe('Enter im Feld schickt ab', seite.url().includes('q=gdb'), seite.url())

    const ersterTreffer = seite.locator('[data-treffer] li a').first()
    pruefe('erster Treffer ist die passende Seite',
        (await ersterTreffer.getAttribute('href') || '').includes('grad-der-behinderung'),
        await ersterTreffer.getAttribute('href'))

    await seite.context().close()

    // Der Weg von irgendeiner Seite zur Suche.
    for (const breite of [390, 1400]) {
        const s = await oeffnen({ breite, pfad: '/verein' })
        const knopf = s.locator('header a[href$="/suche"]')

        pruefe(`${breite}px: Suche ist vom Kopf aus erreichbar`, await knopf.isVisible())

        // Auch wenn nur die Lupe zu sehen ist, muss eine Vorlesehilfe den
        // Zweck kennen — sonst ist es ein Knopf ohne Namen.
        const name = (await knopf.innerText()).trim() || (await knopf.getAttribute('aria-label')) || ''
        const versteckt = await knopf.locator('.sr-only').count()
        pruefe(`${breite}px: Suche hat einen lesbaren Namen`, name.length > 0 || versteckt > 0, name)

        await s.context().close()
    }
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
