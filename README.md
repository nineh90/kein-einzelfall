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

Kümmert sich um alles: PostgreSQL starten, fehlende Abhängigkeiten nachziehen,
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
| `/module-demo` | Vorschau der Inhaltsmodule (`noindex`, fliegt später raus) |

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

---

## Stack

Laravel 12 · PHP 8.3 · PostgreSQL · Blade · Tailwind 4 · Alpine.js

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
(nicht im Git). PostgreSQL startet im Container nicht automatisch mit —
`bin/start` erledigt das.
