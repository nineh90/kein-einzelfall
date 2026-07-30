# KE!N EINZELFALL e.V. — Website

Relaunch der Website des Opferhilfe-Vereins KE!N EINZELFALL e.V. (Hamburg).
WordPress → Laravel. Umsetzung: [Nils-Digital](https://nils-digital.de).

**Dokumentation:** [`docs/Projektplan_KEIN-EINZELFALL_Relaunch.md`](docs/Projektplan_KEIN-EINZELFALL_Relaunch.md)
· [`docs/Komponenten.md`](docs/Komponenten.md)

---

## Starten

```bash
bin/start
```

Kümmert sich um alles: MariaDB starten, fehlende Abhängigkeiten nachziehen,
Migrationen laufen lassen, Assets bauen, Server starten.

Dann im Browser: **http://localhost:8000**
Falls das nicht geht (Docker ohne Port-Mapping), die Container-IP nutzen —
`bin/start` zeigt sie beim Start an.

```bash
bin/start --watch      # Vite beobachtet Änderungen, Reload genügt
PORT=8080 bin/start    # anderer Port
```

### Was man sich ansehen kann

| Pfad | Inhalt |
|---|---|
| `/` | Startseite aus Blöcken |
| `/verein`, `/spenden`, … | die 23 Inhaltsseiten aus der Datenbank |
| `/admin` | Verwaltung (Filament) |
| `/module-demo` | Vorschau der Inhaltsmodule (`noindex`, fliegt später raus) |

### Verwaltung

`/admin` — `bin/start` legt beim ersten Lauf ein Konto an, falls noch keines
existiert:

| | |
|---|---|
| E-Mail | `admin@kein-einzelfall.test` |
| Passwort | `kein-einzelfall` |

Nur für die lokale Entwicklung. In Produktion wird das erste Konto von Hand
angelegt (der Seeder überspringt sich dort selbst).

Weitere Konten:

```bash
php artisan make:filament-user
# danach freischalten — Zugang ist standardmäßig gesperrt:
php artisan tinker --execute='App\Models\User::where("email","…")->update(["panel_zugang" => true]);'
```

Der Zugang muss ausdrücklich erteilt werden. Sobald Vereinsmitglieder eigene
Konten bekommen, liegen sie in derselben Tabelle wie die Redaktion — ohne diesen
Riegel käme jedes Mitglied an die Anfragen.

### Voraussetzungen

PHP **8.2 bis 8.4** mit den Erweiterungen `intl`, `mbstring`, `pdo_mysql`,
`curl`, `xml`. `bin/start` prüft das und nennt den passenden Befehl.

`intl` ist nicht verhandelbar: Filament formatiert damit die Zahlen in jeder
Tabelle. Ohne die Erweiterung liefert jede Liste im Panel einen Fehler.

<details>
<summary>dnf meldet „Verifizierung der Signatur fehlgeschlagen"</summary>

Dann fehlt der Signaturschlüssel der Fedora-Version, aus der das Paket stammt —
auf Nobara kommen Pakete oft aus einer neueren Fedora-Generation als das
installierte System.

```bash
sudo rpm --import https://fedoraproject.org/fedora.gpg
```

**Vorher prüfen, was dnf sonst noch mitnimmt.** Wenn in der Liste `php-cli`
mit einer höheren Nebenversion steht (etwa 8.4 → 8.5), wird die PHP-Version
mit angehoben. Laravel 12 ist auf 8.2 bis 8.4 getestet, deshalb begrenzt
`composer.json` die Spanne. Nur die Erweiterung nachziehen, ohne PHP zu
aktualisieren:

```bash
sudo dnf install --setopt=install_weak_deps=False php-intl-$(php -r 'echo PHP_VERSION;')
```

Geht das nicht, ist ein Container die ruhigere Lösung als ein PHP-Upgrade
mitten im Projekt.
</details>

### Nach einem `git pull`

**`bin/start` neu starten.** Neuer Code bringt oft neue Datenbanktabellen mit;
ein laufender Server führt keine Migrationen aus. Symptome sonst: SQL-Fehler
wie „Table … doesn't exist", fehlende Inhalte oder Bedienelemente, die nicht
reagieren, weil die Assets noch die alten sind.

`bin/start` führt ausstehende Migrationen aus, trägt fehlende Inhalte nach und
baut die Assets neu — und bricht ab, wenn dabei etwas schiefgeht.

### Inhalte neu von der Altseite holen

```bash
php artisan altseite:holen                    # Bestand → docs/altseite-inhalt.json
php artisan db:seed --class=AltseiteSeeder    # JSON → Datenbank
```

### Selbst ausprobieren

- **Notausgang** — Button oben rechts, in der Mobil-Leiste unten, oder **3× ESC**.
  Führt auf wetter.com und ersetzt den History-Eintrag.
- **Darstellung** — das runde Symbol neben „Notausgang": Schriftgröße, 4 Kontrastmodi,
  Leselinie, Legasthenie-Schrift und mehr. Bleibt über Seitenwechsel erhalten.
- **Nur mit der Tastatur** — Tab drücken: erst kommt „Zum Inhalt springen", dann
  öffnen die Menüs per Fokus. Ohne Maus vollständig bedienbar.
- **Ohne JavaScript** — im Browser abschalten. Navigation und Notausgang funktionieren
  weiter. Auf der alten Seite gibt es beides ohne JS nicht.
- **Mobil** — Fenster schmal ziehen: Burger-Menü und die Sticky-Leiste unten erscheinen.

---

## Tests

```bash
php artisan test
```

Die Tests in `tests/Feature/BarrierefreiheitTest.php` sichern das ab, was für die
Zielgruppe nicht verhandelbar ist: Navigation im Server-HTML, Notausgang ohne
JavaScript, keine externen Requests, saubere Überschriften-Gliederung.

### Im echten Browser

```bash
npx playwright install chromium   # einmalig
npm run test:browser              # Server muss laufen
```

Bedient die Seite in einem echten Chromium — mit und ohne JavaScript, mit Maus
und nur mit der Tastatur — und liest dabei die Browser-Konsole mit.

Das ist kein Luxus: `php artisan test` prüft das ausgelieferte HTML und kann
prinzipiell nicht sehen, ob ein Knopf im Browser auch etwas bewirkt. Genau dort
lag ein Fehler, der lange unentdeckt blieb — Details in
[`docs/Komponenten.md`](docs/Komponenten.md), Abschnitt „Behobener Fehler:
Einstellungsknopf reagierte nicht".

### WCAG 2.1 AA messen

```bash
npm run test:a11y                 # Server muss laufen
```

Prüft mit **axe-core** neun repräsentative Seiten in jeder freigeschalteten
Sprache, dazu die vier Zustände der Darstellungs-Einstellungen (Panel offen,
hoher Kontrast, Dunkelmodus, größte Schrift) und den Reflow bei 320 px.

„Maximal barrierefrei" ist die zentrale Zusage dieses Auftrags — ohne Messung
ist sie nicht abnahmefähig. Der erste Lauf fand neun Verstöße, darunter einen
Fußbereich, den ausgerechnet der Modus „hoher Kontrast" unlesbar machte.

**Ein grüner Lauf heißt nicht „barrierefrei".** axe-core findet je nach Quelle
30–50 % der Verstöße: fehlende Alternativtexte, zu schwache Kontraste, falsche
ARIA-Rollen. Es sieht nicht, ob ein Alternativtext etwas Sinnvolles sagt, ob die
Reihenfolge logisch ist oder ob die Sprache verständlich bleibt. Der manuelle
Durchgang und ein Test mit einer echten Vorlesehilfe bleiben nötig.

---

## Dokumente der Altseite

```bash
php artisan dokumente:holen            # nur die verlinkten (31 Dateien, 7,3 MB)
php artisan dokumente:holen --pruefen  # nichts laden, nur berichten was fehlt
php artisan dokumente:holen --alle     # kompletter Bestand (121 Dateien, 26 MB)
```

Lädt die PDFs von der WordPress-Altseite nach `public/dokumente/` und prüft
dabei Größe und Dateityp gegen `docs/dokumente-manifest.json`. Der Lauf ist
wiederholbar — was vollständig vorliegt, wird übersprungen.

**Standard sind nur die verlinkten Dateien.** Der Medienbestand enthält 121
Dokumente, verlinkt sind 31. Unter den übrigen sind laut Übergabe-Checkliste
(A4) auch Behörden-Schriftwechsel, bei denen erst zu prüfen ist, ob
personenbezogene Daten darin stehen. Etwas zu übernehmen, das niemand
angefordert hat, ist bei dieser Zielgruppe das falsche Standardverhalten.

Die alten Adressen `/wp-content/uploads/…` leiten dauerhaft (301) auf die neuen
weiter — sie sind indexiert und stehen in PDFs, die der Verein verschickt hat.

---

## Stack

Laravel 12 · PHP 8.2–8.4 · MySQL/MariaDB · Blade · Tailwind 4 · kein JS-Framework

Bewusste Entscheidungen:

- **Keine externen Requests.** Schriften liegen self-hosted in `public/fonts`.
  Ein Test bricht, wenn sich das ändert.
- **Serverseitig gerendert.** Blog-Filter später über GET-Parameter, nicht Livewire —
  Crawler und Screenreader kommen damit besser zurecht.
- **Notausgang ohne Framework-Abhängigkeit**, als Inline-Script im `<head>`.
- **Alles in `rem`.** Ohne das ist ein Schriftgrößenregler technisch unmöglich.

---

## Aufbau

```
config/navigation.php    Menüstruktur (wandert später in die DB)
config/hilfe.php         Notfallnummern
resources/views/
  components/ui/         Button, Icon, Eyebrow, Section-Head
  components/blocks/     Inhaltsblöcke (= Block-Typen im späteren CMS)
  components/layout/     Header, Footer, Mobile-Bar, A11y-Toolbar, Notausgang
resources/css/
  app.css                Design-Tokens aus dem Mockup
  a11y.css               Kontrastmodi und Darstellungsoptionen
  fonts.css              generiert, nicht per Hand ändern
docs/                    Projektplan, Mockup, Dokumenten-Inventar
storage/app/migration/   gesicherter Bestand der Altseite (nicht im Git)
```

### Datenbank

Lokal: `kein_einzelfall`, Benutzer `ke_dev`. Zugangsdaten in `.env`
(nicht im Git). MariaDB startet weder im Container noch auf einem frisch
installierten System automatisch mit — `bin/start` erledigt das.

Für Tests wird eine zweite Datenbank `kein_einzelfall_test` genutzt (siehe
`phpunit.xml`). Anlegen:

```sql
CREATE DATABASE kein_einzelfall_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON kein_einzelfall_test.* TO 'ke_dev'@'localhost';
```
