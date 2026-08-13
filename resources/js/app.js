/*
 * Bewusst minimal: kein Framework, kein axios, keine SPA-Schicht.
 * Die Seite ist serverseitig gerendert; JavaScript übernimmt hier nur noch die
 * Darstellungs-Toolbar. Menü und alle Aufklapper sind natives <details> und
 * kommen ohne aus.
 *
 * Warum kein Alpine mehr:
 * Unsere Content-Security-Policy erlaubt kein 'unsafe-eval'. Alpine wertet aber
 * jeden Ausdruck in @click, x-show, x-text … zur Laufzeit über new Function()
 * aus. Der Browser blockiert das — ohne sichtbare Meldung für den Benutzer, die
 * Knöpfe reagieren einfach nicht mehr. Die Alternativen wären gewesen, die CSP
 * aufzuweichen (bei einer Seite mit Art.-9-Daten der falsche Handel) oder auf
 * Alpines CSP-Build auszuweichen, der genau die dynamischen Ausdrücke verbietet,
 * für die wir Alpine überhaupt eingesetzt hatten. Für zwei Bedienelemente ist
 * eigener Code die ehrlichere Lösung — und spart nebenbei das ganze Bundle.
 *
 * Wichtig: Der Notausgang hängt an keinem davon — der läuft als eigenständiges
 * Inline-Script im <head>. Wenn Vite hier ausfällt, muss er trotzdem funktionieren.
 */
import { toolbarVerdrahten, leselinieVerdrahten } from './a11y'
import { triggerWarnungVerdrahten } from './trigger-warnung'

toolbarVerdrahten()
leselinieVerdrahten()

// Steht bewusst zuletzt. Scheitert der Aufruf, bleibt der Hinweis als lesbarer
// Block stehen und lässt sich nur nicht wegklicken — unschön, aber harmlos.
// Andersherum bliebe bei einem Fehler die Darstellungs-Toolbar unverdrahtet,
// und die brauchen manche Menschen, um überhaupt lesen zu können.
triggerWarnungVerdrahten()
