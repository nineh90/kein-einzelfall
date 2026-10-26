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
 * `bereich: false` macht dasselbe für die Warnung vor einem Bereich (KEV-156).
 *
 * `trigger: false` bestellt die vorgeschaltete Trigger-Warnung vorab ab —
 * so, wie es jemand tut, der sie einmal weggeklickt hat. Das ist für fast
 * alle Prüfungen hier der richtige Ausgangszustand: Der Hinweis ist ein
 * modaler Dialog, hält den Fokus fest und läge sonst vor jedem Knopf, um den
 * es in diesem Test geht. Die Warnung selbst wird weiter unten eigens geprüft.
 */
async function oeffnen({ js = true, breite = 1400, hoehe = 900, pfad = '/', trigger = false, bereich = false } = {}) {
    const kontext = await browser.newContext({
        javaScriptEnabled: js,
        viewport: { width: breite, height: hoehe },
    })

    if (!trigger && js) {
        await kontext.addInitScript(() => localStorage.setItem('ke.trigger.aus', '1'))
    }

    // Dasselbe für die Warnungen vor „Raum für Ungesagtes“ (KEV-156) und den
    // Abkürzungen im sexuellen Kontext (KEV-154): in diesem Besuch schon
    // bestätigt. Geprüft wird sie weiter unten eigens.
    if (!bereich && js) {
        await kontext.addInitScript(() => sessionStorage.setItem('ke.bereichswarnung', '["raum-fuer-ungesagtes","abkuerzungen-im-sexuellen-kontext"]'))
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
    // Zwei Knöpfe öffnen das Panel (KEV-26): das Tab am Rand ab „lg“ und
    // der Eintrag in der unteren Leiste darunter. Je Breite ist einer sichtbar.
    const knopf = seite.locator('button[aria-controls="a11y-panel"]:visible')
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

    pruefe('Zähler am Knopf stimmt', await seite.locator('[data-a11y-zaehler]:visible').textContent() === '3')

    // Der eigentliche Zweck der Einstellungen: Sie müssen bleiben.
    await seite.goto(BASIS + '/wissen', { waitUntil: 'networkidle' })
    pruefe('überlebt den Seitenwechsel',
        await seite.evaluate(() => document.documentElement.dataset.kontrast) === 'hoch')

    await seite.locator('button[aria-controls="a11y-panel"]:visible').click()
    await seite.locator('[data-a11y-zuruecksetzen]').click()
    pruefe('Zurücksetzen räumt alles ab',
        await seite.evaluate(() => document.documentElement.dataset.kontrast) === ''
        && !(await seite.evaluate(() => document.documentElement.classList.contains('a11y-leselinie'))))
}

console.log('\nDarstellungs-Toolbar (Handy, KEV-26)')
{
    const seite = await oeffnen({ breite: 375 })
    const tab = seite.locator('button.fixed[aria-controls="a11y-panel"]')
    const leiste = seite.locator('nav [data-a11y-oeffnen]')
    const panel = seite.locator('#a11y-panel')

    pruefe('kein Tab am Rand, das über dem Text liegt', !(await tab.isVisible()))
    pruefe('der Knopf steht in der unteren Leiste', await leiste.isVisible())

    await leiste.click()
    pruefe('er öffnet das Panel', await panel.isVisible())
    pruefe('aria-expanded wird mitgeführt', await leiste.getAttribute('aria-expanded') === 'true')

    // Das Panel darf die Leiste nicht verdecken: Der Notausgang liegt darin.
    const p = await panel.boundingBox()
    const l = await seite.locator('nav[aria-label] >> nth=-1').boundingBox()
    const exit = await seite.locator('nav [data-notausgang]').boundingBox()
    pruefe('das Panel lässt den Notausgang frei', p.y + p.height <= exit.y + 1, JSON.stringify({ p, exit, l }))

    await seite.keyboard.press('Escape')
    pruefe('Escape schliesst', !(await panel.isVisible()))
    pruefe('der Fokus kehrt zum Knopf in der Leiste zurück',
        await seite.evaluate(() => document.activeElement?.closest('nav') !== null
            && document.activeElement.hasAttribute('data-a11y-oeffnen')))
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
    // Ohne Skript öffnet der Knopf nichts, also steht er gar nicht erst da.
    pruefe('Darstellungs-Knopf in der Leiste bleibt verborgen',
        !(await mobil.locator('nav [data-a11y-oeffnen]').isVisible()))

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

console.log('\nTrigger-Warnung: passt auf den Laptop-Bildschirm')
{
    /*
     * Taddis Rückmeldung (KEV-164): Der Hinweis reichte über den Bildschirm
     * hinaus, oben fehlte die Überschrift, unten lagen die Knöpfe unter der
     * Kante. 1366 × 650 ist ein gängiger Laptop mit Browserleisten.
     */
    const seite = await oeffnen({ trigger: true, breite: 1366, hoehe: 650 })
    const masse = await seite.evaluate(() => {
        const d = document.getElementById('trigger-warnung')
        return { inhalt: d.scrollHeight, fenster: d.clientHeight }
    })

    pruefe('der ganze Hinweis ist ohne Scrollen zu sehen',
        masse.inhalt <= masse.fenster,
        `${masse.inhalt} px Inhalt, ${masse.fenster} px Fenster`)

    // Auf dem Handy passt er nicht ganz hinein. Dann soll er oben beginnen,
    // nicht beim Kästchen, auf das showModal() den Fokus setzt.
    const mobil = await oeffnen({ trigger: true, breite: 390, hoehe: 750 })
    const oben = await mobil.evaluate(() => document.getElementById('trigger-warnung').scrollTop)

    pruefe('auf dem Handy beginnt er oben', oben === 0, `scrollTop ${oben}`)
}

console.log('\nHilfe-Box: Notrufe im Raster der Nummern')
{
    // Taddis Wunsch (KEV-169): Die 112 steht bündig unter der rechten Spalte.
    const seite = await oeffnen()
    const x = async (tel) => (await seite.locator(`section[aria-labelledby="hilfe-titel"] a[href="tel:${tel}"]`).boundingBox()).x
    const rechts = await x('+49116006')
    const notruf = await x('112')

    pruefe('die 112 steht bündig unter der 116 006', Math.abs(rechts - notruf) < 1,
        `116 006 bei ${Math.round(rechts)} px, 112 bei ${Math.round(notruf)} px`)
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

// --- Triggerwarnung vor einem Bereich (KEV-156) ------------------------------

console.log('\nTriggerwarnung vor „Raum für Ungesagtes“')
{
    // Erster Besuch: erst der allgemeine Hinweis, dann die Warnung des Bereichs,
    // nie beide übereinander.
    const s = await oeffnen({ trigger: true, bereich: true, pfad: '/blickwinkel' })
    const allgemein = s.locator('#trigger-warnung')
    const warnung = s.locator('#bereichswarnung')

    pruefe('erst der allgemeine Hinweis, die Warnung wartet',
        await allgemein.isVisible() && !(await warnung.isVisible()))

    await allgemein.locator('[data-trigger-weiter]').click()
    // Das close-Ereignis kommt asynchron, erst danach öffnet sie.
    await warnung.waitFor({ state: 'visible', timeout: 2000 }).catch(() => {})
    pruefe('danach die Warnung vor dem Bereich, als modaler Dialog',
        await warnung.isVisible() && await s.evaluate(() => document.getElementById('bereichswarnung').matches(':modal')))
    pruefe('der Fokus liegt in ihr',
        await s.evaluate(() => document.getElementById('bereichswarnung').contains(document.activeElement)))

    const knoepfe = await Promise.all(['[data-bereichswarnung-weiter]', 'a[href="/"]', 'a[data-notausgang]']
        .map((w) => warnung.locator(w).boundingBox()))
    pruefe('drei Wege, gleich hoch, auf einer Linie',
        knoepfe.every((k) => k && Math.abs(k.height - knoepfe[0].height) < 1 && Math.abs(k.y - knoepfe[0].y) < 1),
        JSON.stringify(knoepfe.map((k) => k && [Math.round(k.y), Math.round(k.height)])))

    await warnung.locator('[data-bereichswarnung-weiter]').click()
    await s.waitForTimeout(100)
    pruefe('„weiter“ schliesst sie', !(await warnung.isVisible()))
    pruefe('gemerkt wird nur der Bereich, nur für diesen Besuch',
        await s.evaluate(() => sessionStorage.getItem('ke.bereichswarnung') === '["raum-fuer-ungesagtes"]'
            && !Object.keys(localStorage).some((k) => k.includes('bereich'))))

    await s.goto(BASIS + '/unueberhoerbar', { waitUntil: 'networkidle' })
    pruefe('auf den übrigen Seiten des Bereichs nicht noch einmal', (await s.locator('#bereichswarnung').count()) === 0)
    await s.context().close()

    // Neuer Besuch: Sie kommt wieder, auch wenn der allgemeine Hinweis
    // abbestellt ist. Escape gilt wie „weiter“.
    const wieder = await oeffnen({ bereich: true, pfad: '/raum-fuer-ungesagtes' })
    pruefe('kommt beim nächsten Besuch wieder', await wieder.locator('#bereichswarnung').isVisible())
    await wieder.keyboard.press('Escape')
    await wieder.waitForTimeout(100)
    await wieder.reload({ waitUntil: 'networkidle' })
    pruefe('per Escape geschlossen gilt sie als bestätigt', (await wieder.locator('#bereichswarnung').count()) === 0)
    await wieder.context().close()

    // Wer den Bereich jetzt nicht sehen will, muss nicht die Website verlassen.
    const zurueck = await oeffnen({ bereich: true, pfad: '/wall-of-shame' })
    await zurueck.locator('#bereichswarnung a[href="/"]').click()
    await zurueck.waitForLoadState('networkidle')
    pruefe('„Zurück zur Startseite“ führt hinaus, ohne zu bestätigen',
        new URL(zurueck.url()).pathname === '/'
        && await zurueck.evaluate(() => sessionStorage.getItem('ke.bereichswarnung') === null))
    await zurueck.context().close()

    const mobil = await oeffnen({ bereich: true, breite: 390, pfad: '/blickwinkel' })
    const mk = await Promise.all(['[data-bereichswarnung-weiter]', 'a[href="/"]', 'a[data-notausgang]']
        .map((w) => mobil.locator('#bereichswarnung').locator(w).boundingBox()))
    pruefe('mobil untereinander und gleich breit',
        mk.every((k, i) => Math.abs(k.width - mk[0].width) < 1 && (i === 0 || k.y > mk[i - 1].y + mk[i - 1].height - 1))
        && await mobil.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth))
    await mobil.context().close()

    const ohne = await oeffnen({ js: false, pfad: '/wall-of-shame' })
    const ow = ohne.locator('#bereichswarnung')
    pruefe('ohne JS: steht sichtbar vor dem Inhalt',
        await ow.isVisible() && (await ow.boundingBox()).y < (await ohne.locator('h1').boundingBox()).y)
    pruefe('ohne JS: Zurück und Notausgang da, „weiter“ verborgen, Hinweis warum',
        await ow.locator('a[href="/"]').isVisible() && await ow.locator('a[data-notausgang]').isVisible()
        && !(await ow.locator('[data-bereichswarnung-weiter]').isVisible())
        && await ow.locator('[data-bereichswarnung-ohne-js]').isVisible())
    await ohne.context().close()

    const anderswo = await oeffnen({ bereich: true, pfad: '/wissen' })
    pruefe('ausserhalb des Bereichs keine', (await anderswo.locator('#bereichswarnung').count()) === 0)
    await anderswo.context().close()
}

console.log('\nSpendenhinweis wartet auch auf die Warnung des Bereichs')
{
    const kontext = await browser.newContext({ viewport: { width: 1400, height: 900 } })
    await kontext.addInitScript(() => localStorage.setItem('ke.spenden.aufrufe', '99'))
    const seite = await kontext.newPage()
    seite.on('pageerror', (e) => meldungen.push(`[bereich+hinweis] ${e.message}`))
    await seite.goto(BASIS + '/blickwinkel', { waitUntil: 'networkidle' })

    await seite.locator('#trigger-warnung [data-trigger-weiter]').click()
    await seite.waitForTimeout(100)
    pruefe('nach dem allgemeinen Hinweis noch nicht', !(await seite.locator('#spendenhinweis').isVisible()))

    await seite.locator('#bereichswarnung [data-bereichswarnung-weiter]').click()
    await seite.waitForTimeout(100)
    pruefe('erst nach der Warnung des Bereichs', await seite.locator('#spendenhinweis').isVisible())
    await kontext.close()
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

    await seite.locator('button[aria-controls="a11y-panel"]:visible').click()
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
//
// Geprueft auf „Vorstand und Team“: Seit KEV-120 steht das Band nicht mehr
// auf der Startseite, sondern auf einzelnen Unterseiten
// (UnterstuetzungsbandSeeder), seit KEV-175 nicht mehr auf /verein.

console.log('\nVereinsname im Hinweisband')
{
    for (const breite of [320, 390, 768, 1400]) {
        const seite = await oeffnen({ breite, pfad: '/ueber-uns-vorstand-und-team' })

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

// --- Galerie einer Messe (KEV-132) -----------------------------------------

console.log('\nGalerie einer Messe')
{
    const pfad = '/oeffentlichkeitsarbeit/ehrenamtsmesse-berlin-2026'

    for (const breite of [390, 1400]) {
        const s = await oeffnen({ breite, pfad })
        const links = s.locator('a[data-galerie-bild]')
        const gesamt = await links.count()
        const zaehler = s.locator('dialog [aria-live]')

        await links.nth(1).click()
        pruefe(`${breite}px: Klick öffnet die große Ansicht`, await s.locator('dialog[open]').count() === 1)
        pruefe(`${breite}px: Zähler nennt das Foto`, (await zaehler.innerText()).trim() === `2 von ${gesamt}`)

        await s.keyboard.press('ArrowRight')
        pruefe(`${breite}px: Pfeiltaste blättert weiter`, (await zaehler.innerText()).trim() === `3 von ${gesamt}`)

        await s.locator('dialog button[aria-label="Vorheriges Foto"]').click()
        pruefe(`${breite}px: Knopf blättert zurück`, (await zaehler.innerText()).trim() === `2 von ${gesamt}`)

        await s.keyboard.press('Escape')
        pruefe(`${breite}px: Esc schließt`, await s.locator('dialog[open]').count() === 0)
        const fokus = await s.evaluate(() => document.activeElement?.hasAttribute('data-galerie-bild'))
        pruefe(`${breite}px: Fokus kehrt zum Foto zurück`, fokus === true)

        await s.context().close()
    }

    // Ohne JavaScript bleibt jedes Foto ein Link auf die große Fassung.
    const ohne = await oeffnen({ js: false, pfad })
    const ziel = await ohne.locator('a[data-galerie-bild]').nth(1).getAttribute('href')
    pruefe('ohne JS: Foto ist ein Link auf die Bilddatei', /\.webp(\?|$)/.test(ziel ?? ''), ziel ?? '')
    await ohne.context().close()
}

// --- Kopfzeile mit allen Bereichen (KEV-159, seit KEV-160 sieben) ----------

console.log('\nKopfzeile mit sieben Bereichen')
{
    for (const js of [true, false]) {
        for (const breite of [1280, 1440, 1920]) {
            const s = await oeffnen({ breite, pfad: '/verein', js })
            const art = js ? '' : ' ohne JS'
            const zustand = await s.evaluate(() => {
                const logo = document.querySelector('[data-kopf-zeile] > a').getBoundingClientRect()
                const menue = document.querySelector('[data-kopf-menue]').getBoundingClientRect()
                const exit = document.querySelector('header [data-notausgang]').getBoundingClientRect()
                return {
                    zweiteZeile: menue.top >= logo.bottom - 1,
                    ueberlauf: document.documentElement.scrollWidth > window.innerWidth,
                    exitImBild: exit.right <= window.innerWidth,
                }
            })
            pruefe(`${breite}px${art}: Menüleiste in zweiter Zeile statt Burger`, zustand.zweiteZeile
                && await s.locator('[data-kopf-menue]').isVisible() && !(await s.locator('[data-kopf-burger]').isVisible()))
            pruefe(`${breite}px${art}: kein seitliches Scrollen, Notausgang im Bild`, !zustand.ueberlauf && zustand.exitImBild)
            pruefe(`${breite}px${art}: alle sieben Bereiche sichtbar`, await s.locator('[data-kopf-menue] > ul > li:visible').count() === 7)
            await s.context().close()
        }
    }

    // Größte Schrift: zweite Zeile oder Burger, je nach Breite. Entscheidend
    // ist, dass nichts überläuft und der Notausgang im Bild bleibt.
    for (const breite of [1280, 1440]) {
        const s = await oeffnen({ breite, pfad: '/verein' })
        await s.locator('button[aria-controls="a11y-panel"]:visible').click()
        await s.locator('[data-a11y-setzen="schrift"][data-a11y-wert="3"]').click()
        await s.waitForTimeout(300)
        const exit = await s.locator('header [data-notausgang]:visible').first().boundingBox()
        pruefe(`${breite}px, größte Schrift: nichts läuft über, Notausgang im Bild`,
            await s.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)
            && exit !== null && exit.x + exit.width <= breite)
        await s.context().close()
    }
}

// --- Wall of Shame (KEV-157) ----------------------------------------------

console.log('\nWall of Shame')
{
    for (const breite of [390, 1400]) {
        const s = await oeffnen({ breite, pfad: '/wall-of-shame' })
        const striche = () => s.locator('[data-wand-striche] line').count()
        const zaehler = s.locator('[data-wand-zaehler]')
        const gesamt = await s.locator('[data-wand-liste] li').count()

        pruefe(`${breite}px: Wand ist da, noch kein Strich`, await s.locator('[data-wand]').isVisible() && await striche() === 0)

        await s.locator('[data-wand-stapel]').click()
        pruefe(`${breite}px: Klick zeigt den ersten Satz und setzt einen Strich`,
            (await s.locator('[data-wand-satz]').innerText()).startsWith('„So schlimm')
            // textContent: innerText gäbe die Großbuchstaben aus dem CSS zurück.
            && await striche() === 1 && (await zaehler.textContent()).trim() === `1 von ${gesamt}`)

        // Weiter mit der Tastatur: Der Fokus bleibt auf dem Stapel.
        await s.locator('[data-wand-stapel]').focus()
        for (let i = 0; i < 4; i++) await s.keyboard.press('Enter')
        pruefe(`${breite}px: fünf Sätze, fünf Striche, Fokus bleibt`, await striche() === 5
            && await s.evaluate(() => document.activeElement?.hasAttribute('data-wand-stapel')))

        // Alles lesen: Die Striche passen auf die Wand, nichts läuft über.
        for (let i = 5; i < gesamt; i++) await s.locator('[data-wand-stapel]').click()
        const passt = await s.evaluate(() => {
            const wand = document.querySelector('[data-wand]').getBoundingClientRect()
            const linien = [...document.querySelectorAll('[data-wand-striche] line')].map((l) => l.getBoundingClientRect())
            return linien.every((r) => r.left >= wand.left - 2 && r.right <= wand.right + 2 && r.top >= wand.top - 2 && r.bottom <= wand.bottom + 2)
                && document.documentElement.scrollWidth <= window.innerWidth
        })
        pruefe(`${breite}px: alle ${gesamt} Striche auf der Wand, nichts läuft über`, await striche() === gesamt && passt)

        await s.locator('[data-wand-stapel]').click()
        pruefe(`${breite}px: danach der Schluss`, (await s.locator('[data-wand-satz]').innerText()).includes(`alle ${gesamt} Sätze`)
            && (await s.locator('[data-wand-knopf]').innerText()).trim() === 'Noch einmal von vorn')

        await s.locator('[data-wand-stapel]').click()
        pruefe(`${breite}px: von vorn`, await striche() === 1)
        await s.context().close()
    }

    // Lesestand (KEV-163): Nach dem Neuladen geht es weiter, wo man war.
    {
        const s = await oeffnen({ pfad: '/wall-of-shame' })
        const striche = () => s.locator('[data-wand-striche] line').count()
        const zaehler = s.locator('[data-wand-zaehler]')
        const satz = s.locator('[data-wand-satz]')
        const vonVorn = s.locator('[data-wand-von-vorn]')
        const gesamt = await s.locator('[data-wand-liste] li').count()

        pruefe('Lesestand: anfangs nichts gespeichert, kein „Von vorn“',
            await s.evaluate(() => localStorage.getItem('ke.wand')) === null && !(await vonVorn.isVisible()))

        for (let i = 0; i < 7; i++) await s.locator('[data-wand-stapel]').click()
        const achter = await s.locator('[data-wand-liste] li').nth(7).getAttribute('data-satz')
        await s.reload({ waitUntil: 'networkidle' })

        pruefe('Lesestand: nach dem Neuladen 7 Striche, Zettel nennt nur den Stand',
            await striche() === 7 && (await zaehler.textContent()).trim() === `7 von ${gesamt}`
            && (await satz.innerText()).startsWith('Du hast schon 7 von') && await vonVorn.isVisible())

        await s.locator('[data-wand-stapel]').click()
        pruefe('Lesestand: Klick zeigt Satz 8', (await satz.innerText()) === `„${achter}“` && await striche() === 8)

        const bis = await s.evaluate(() => Object.values(JSON.parse(localStorage.getItem('ke.wand')))[0].bis - Date.now())
        pruefe('Lesestand: gilt rund 30 Tage', bis > 29.9 * 864e5 && bis <= 30 * 864e5)

        await vonVorn.click()
        pruefe('Lesestand: „Von vorn beginnen“ leert Wand und Speicher, Fokus auf dem Stapel',
            await striche() === 0 && (await satz.innerText()).startsWith('Ein Klick auf den Papierstapel')
            && !(await vonVorn.isVisible()) && await s.evaluate(() => localStorage.getItem('ke.wand')) === null
            && await s.evaluate(() => document.activeElement?.hasAttribute('data-wand-stapel')))

        // Abgelaufen: wieder von vorn.
        await s.evaluate(() => localStorage.setItem('ke.wand', JSON.stringify({
            [document.querySelector('[data-wall-of-shame]').getAttribute('aria-labelledby')]: { gelesen: 20, gesamt: 145, ende: false, bis: Date.now() - 1000 },
        })))
        await s.reload({ waitUntil: 'networkidle' })
        pruefe('Lesestand: nach Ablauf vergessen', await striche() === 0)

        // Bis zum Schluss gelesen: Nach dem Neuladen steht wieder der Schluss.
        for (let i = 0; i <= gesamt; i++) await s.locator('[data-wand-stapel]').click()
        await s.reload({ waitUntil: 'networkidle' })
        pruefe('Lesestand: bis zum Schluss gelesen, nach dem Neuladen wieder der Schluss',
            await striche() === gesamt && (await satz.innerText()).includes(`alle ${gesamt} Sätze`)
            && (await s.locator('[data-wand-knopf]').innerText()).trim() === 'Noch einmal von vorn')
        await s.context().close()
    }

    const ohne = await oeffnen({ js: false, pfad: '/wall-of-shame' })
    pruefe('ohne JS: keine Wand, aber alle Sätze als Liste', !(await ohne.locator('[data-wand]').isVisible())
        && await ohne.locator('[data-wand-liste] li').count() > 100)
    await ohne.context().close()
}

// --- Anmeldung zu einer Selbsthilfegruppe (KEV-226) -----------------------

console.log('\nAnmeldung zu einer Selbsthilfegruppe')
{
    const s = await oeffnen({ breite: 390, pfad: '/selbsthilfegruppen/wir-sind-nicht-mehr-stumm' })

    await s.locator('a[href="#anmeldung"]').click()
    await s.waitForTimeout(400)
    const imBild = await s.evaluate(() => {
        const r = document.querySelector('#anmeldung-titel').getBoundingClientRect()
        return r.top >= 0 && r.top < window.innerHeight
    })
    pruefe('„Jetzt anmelden“ springt zum Formular', imBild)

    // Ohne Häkchen lässt der Browser nicht absenden (required)
    await s.waitForTimeout(3200)
    await s.fill('#anmeldung-email', 'browsertest@beispiel.de')
    await s.locator('#anmeldung button[type=submit]').click()
    pruefe('ohne Zustimmung kein Absenden', !(await s.locator('#anmeldung [role=status]').count())
        && await s.evaluate(() => !document.querySelector('#anmeldung-teilnahme').validity.valid))

    await s.check('#anmeldung-teilnahme')
    await s.check('#anmeldung-gruppenregeln')
    await s.check('#anmeldung-einwilligung')
    await s.locator('#anmeldung button[type=submit]').click()
    await s.waitForLoadState('networkidle')
    const meldung = s.locator('#anmeldung [role=status]')
    pruefe('nach dem Absenden die Bestätigung, im Blick', await meldung.isVisible()
        && (await meldung.innerText()).includes('Deine Anmeldung ist angekommen')
        && s.url().endsWith('#formular-anmeldung')
        && await s.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth))
    await s.context().close()
}

// --- Suche im Glossar (KEV-153) --------------------------------------------

console.log('\nSuche im Glossar')
{
    for (const breite of [390, 1400]) {
        const s = await oeffnen({ breite, pfad: '/glossar' })
        const sichtbar = () => s.locator('[data-glossar-eintrag]:visible').count()
        const status = s.locator('[data-glossar-status]')
        const gesamt = await sichtbar()

        await s.fill('#glossar-suche', 'sgb')
        await s.waitForTimeout(400)
        const sgb = await sichtbar()
        pruefe(`${breite}px: Tippen filtert die Liste`, sgb > 1 && sgb < 20, `${sgb} von ${gesamt}`)
        pruefe(`${breite}px: Trefferzahl wird angesagt`, (await status.innerText()).trim() === `Treffer: ${sgb} von ${gesamt}`)
        pruefe(`${breite}px: Adresse bleibt ohne Suchwort`, !s.url().includes('suche='), s.url())
        pruefe(`${breite}px: Buchstaben ohne Treffer verschwinden`, await s.locator('[data-glossar-sprung]:visible').count() === 1)

        await s.fill('#glossar-suche', 'idr')
        await s.waitForTimeout(400)
        pruefe(`${breite}px: „idr“ findet „i. d. R.“`, await s.locator('#i-d-r').isVisible())

        await s.fill('#glossar-suche', 'xqzv')
        await s.waitForTimeout(400)
        pruefe(`${breite}px: ohne Treffer der Weg in die Websuche`, await s.locator('[data-glossar-leer]').isVisible()
            && (await s.locator('[data-glossar-websuche]').getAttribute('href')).endsWith('?q=xqzv'))

        await s.locator('[data-glossar-zuruecksetzen]').click()
        pruefe(`${breite}px: „Alle zeigen“ setzt zurück`, await sichtbar() === gesamt)

        await s.context().close()
    }

    // Ohne JavaScript filtert der Server.
    const ohne = await oeffnen({ js: false, pfad: '/glossar' })
    await ohne.fill('#glossar-suche', 'SGB')
    await ohne.press('#glossar-suche', 'Enter')
    await ohne.waitForLoadState('load')
    pruefe('ohne JS: Absenden filtert über die Adresse', ohne.url().includes('suche=SGB')
        && await ohne.locator('[data-glossar-eintrag]:visible').count() < 20)
    await ohne.context().close()
}

// --- Zweites Glossar (KEV-155) ----------------------------------------------

console.log('\nBehörden & Bescheide')
{
    for (const breite of [390, 1400]) {
        const s = await oeffnen({ breite, pfad: '/behoerden-und-bescheide' })
        const sichtbar = () => s.locator('[data-glossar-eintrag]:visible').count()
        const gesamt = await sichtbar()
        pruefe(`${breite}px: alle 128 Begriffe`, gesamt === 128, `${gesamt}`)

        await s.fill('#glossar-suche', 'frist')
        await s.waitForTimeout(400)
        const frist = await sichtbar()
        pruefe(`${breite}px: Tippen filtert auch hier`, frist > 1 && frist < 20
            && (await s.locator('[data-glossar-status]').innerText()).trim() === `Treffer: ${frist} von 128`, `${frist}`)
        pruefe(`${breite}px: nichts läuft über`,
            await s.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth))
        await s.context().close()
    }
}

// --- Fristen (KEV-152) -----------------------------------------------------

console.log('\nFristen und Möglichkeiten')
{
    for (const breite of [390, 1400]) {
        const s = await oeffnen({ breite, pfad: '/fristen' })
        const sichtbar = () => s.locator('details[data-glossar-eintrag]:visible').count()
        pruefe(`${breite}px: alle 19 Fristen`, await sichtbar() === 19)

        await s.fill('#glossar-suche', 'untätigkeit')
        await s.waitForTimeout(400)
        const treffer = await sichtbar()
        pruefe(`${breite}px: Tippen filtert die Fristen`, treffer >= 2 && treffer < 19
            && (await s.locator('[data-glossar-status]').innerText()).trim() === `Treffer: ${treffer} von 19`, `${treffer}`)

        await s.locator('[data-glossar-zuruecksetzen]').click()
        await s.locator('#widerspruch summary').click()
        pruefe(`${breite}px: Karte klappt auf`, await s.locator('#widerspruch').evaluate((d) => d.open)
            && await s.getByText('Muss der Widerspruch sofort begründet werden?').isVisible())

        // Die Kachel springt zum Leitfaden.
        // Die Seite scrollt weich (scroll-behavior: smooth): warten, bis es steht.
        await s.locator('a[href="#frist-bekommen"]').click()
        await s.waitForFunction(() => {
            const oben = document.getElementById('frist-bekommen').getBoundingClientRect().top
            return oben >= 0 && oben < 500
        }, null, { timeout: 4000 }).catch(() => {})
        const kopf = await s.locator('#frist-bekommen').boundingBox()
        pruefe(`${breite}px: Kachel springt zum Leitfaden`, kopf && kopf.y >= 0 && kopf.y < 500, JSON.stringify(kopf))
        pruefe(`${breite}px: nichts läuft über`, await s.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth))
        await s.context().close()
    }

    // Ein Link auf eine Frist klappt ihre Karte auf.
    const link = await oeffnen({ pfad: '/fristen#sachstandsanfrage' })
    pruefe('Link auf eine Frist öffnet die Karte', await link.locator('#sachstandsanfrage').evaluate((d) => d.open)
        && await link.getByText('Was sollte in eine Sachstandsanfrage?').isVisible())
    await link.context().close()
}

// --- Klartext statt Fachsprache (KEV-254) -----------------------------------

console.log('\nKlartext statt Fachsprache')
{
    for (const breite of [390, 1400]) {
        const s = await oeffnen({ breite, pfad: '/klartext-statt-fachsprache' })
        pruefe(`${breite}px: sechs Kacheln`, await s.locator('li.flex.min-w-0 > a').count() === 6)

        // Filtert beim Tippen wie im Glossar, ohne Absenden.
        const status = s.locator('[data-klartext-status]')
        await s.locator('#klartext-suchfeld').pressSequentially('Widerspruch', { delay: 20 })
        await s.waitForFunction(() => /^Treffer: \d+ \(in \d von 6 Kategorien\)/.test(
            document.querySelector('[data-klartext-status]').textContent.trim()), null, { timeout: 5000 }).catch(() => {})
        const text = (await status.innerText()).trim()
        pruefe(`${breite}px: Tippen sucht in allen Kategorien`, /^Treffer: \d+ \(in \d von 6 Kategorien\)/.test(text), text)
        pruefe(`${breite}px: Adresse bleibt`, !s.url().includes('suche='))
        const links = await s.locator('[data-klartext-treffer] a[href*="#"]').count()
        pruefe(`${breite}px: Treffer verlinken ihre Stelle`, links > 3, `${links}`)

        await s.locator('[data-klartext-leeren]').click()
        await s.waitForFunction(() => document.querySelector('[data-klartext-status]').textContent.trim() === '',
            null, { timeout: 3000 }).catch(() => {})
        pruefe(`${breite}px: Suche leeren`, (await status.innerText()).trim() === ''
            && await s.locator('[data-klartext-treffer] section').count() === 0
            && await s.evaluate(() => document.activeElement?.id) === 'klartext-suchfeld')
        pruefe(`${breite}px: nichts läuft über`, await s.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth))
        await s.context().close()
    }

    // Ohne JavaScript: Absenden, die Seite springt zu den Treffern.
    const ohne = await oeffnen({ js: false, breite: 1400, pfad: '/klartext-statt-fachsprache' })
    await ohne.fill('#klartext-suchfeld', 'Widerspruch')
    await ohne.press('#klartext-suchfeld', 'Enter')
    await ohne.waitForLoadState('load')
    pruefe('ohne JavaScript: Absenden zeigt die Treffer', ohne.url().includes('suche=Widerspruch')
        && /^Treffer: /.test((await ohne.locator('[data-klartext-status]').innerText()).trim()))
    await ohne.context().close()
}

// --- Sprungziele unter dem klebenden Kopf (KEV-262) -------------------------

// Ab 1280 px ist der Kopf zweizeilig (127 px) und wächst mit der Schrift.
// Ein angesprungener Abschnitt muss darunter beginnen, nicht dahinter.
console.log('\nSprungziele unter dem Kopf')
{
    const unterDemKopf = (s, ziel) => s.evaluate((id) => {
        const kopf = document.querySelector('header.sticky').getBoundingClientRect().bottom
        const oben = document.getElementById(id).getBoundingClientRect().top
        return { kopf: Math.round(kopf), oben: Math.round(oben) }
    }, ziel)
    const warten = (s, id) => s.waitForFunction((ziel) => {
        const oben = document.getElementById(ziel).getBoundingClientRect().top
        return Math.abs(oben - (window.__vorher ?? -1)) < 1 || ((window.__vorher = oben), false)
    }, id, { polling: 150, timeout: 5000 }).catch(() => {})

    for (const [breite, gross] of [[390, false], [1024, false], [1400, false], [1400, true]]) {
        const s = await oeffnen({ breite, pfad: '/behoerden-und-bescheide' })
        const name = `${breite}px${gross ? ', große Schrift' : ''}`

        if (gross) {
            await s.locator('button[aria-controls="a11y-panel"]:visible').click()
            await s.locator('[data-a11y-setzen="schrift"][data-a11y-wert="3"]').click()
            await s.keyboard.press('Escape')
        }

        await s.getByRole('link', { name: 'Zu den typischen Sätzen' }).click()
        await warten(s, 'typische-saetze')
        const { kopf, oben } = await unterDemKopf(s, 'typische-saetze')
        pruefe(`${name}: Überschrift sichtbar unter dem Kopf`, oben >= kopf && oben < kopf + 60, `Kopf ${kopf}, Ziel ${oben}`)
        await s.context().close()
    }

    // „Auf dieser Seite“: Der angesprungene Abschnitt ist danach markiert.
    const artikel = await oeffnen({ pfad: '/anuas' })
    await artikel.locator('[data-verzeichnis] a[href="#abschnitt-projektdokumentation"]').click()
    await warten(artikel, 'abschnitt-projektdokumentation')
    const { kopf, oben } = await unterDemKopf(artikel, 'abschnitt-projektdokumentation')
    pruefe('Verzeichnis: Abschnitt unter dem Kopf', oben >= kopf && oben < kopf + 60, `Kopf ${kopf}, Ziel ${oben}`)
    pruefe('Verzeichnis: angesprungener Abschnitt markiert',
        await artikel.locator('[data-verzeichnis] a[href="#abschnitt-projektdokumentation"]').getAttribute('aria-current') === 'true')
    await artikel.context().close()
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
