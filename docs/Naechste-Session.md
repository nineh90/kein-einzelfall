# Auftrag für die nächste Session

Kopiervorlage. Stand: 27.07.2026, nach der Mehrsprachigkeits-Session.

---

## Kontext

Du arbeitest an `/workspace/kein-einzelfall` — dem Laravel-Relaunch der Website von
**KE!N EINZELFALL e.V.** (Opferhilfe-Verein, Hamburg). Auftraggeberin ist Tatjana Belmar,
Umsetzung Nils-Digital (Kevin). Lies zuerst:

- `docs/Projektplan_KEIN-EINZELFALL_Relaunch.md` — Auftrag, Stack, Meilensteine, Risiken
- `docs/Komponenten.md` — was gebaut ist und warum, inkl. der behobenen Fehler
- `docs/Uebergabe-Checkliste.md` — offene Punkte für Kevin und den Verein
- `README.md` — Start, Tests, Voraussetzungen

**Stand:** Meilensteine 1–9 fertig, Mehrsprachigkeit im Grundgerüst fertig.
24 Seiten, 10 Gruppen, 26 Weiterleitungen. **161 Tests / 1145 Assertions.**
Starten mit `bin/start`. Drei Testläufe:

```bash
php artisan test          # 161 Tests
npm run test:browser      # Bedienung im echten Chromium
npm run test:a11y         # axe-core, WCAG 2.1 AA, 36 Durchläufe
```

**Vier Rahmenbedingungen, die nicht verhandelbar sind:**

1. **Inhalte werden nicht erfunden.** Der komplette Bestand der Altseite wird übernommen.
   `ContentTreueTest` prüft das in beide Richtungen. Für Übersetzungen gilt dasselbe,
   nur schärfer — siehe unten.
2. **Die CSP erlaubt kein `'unsafe-eval'`.** Deshalb kein JavaScript-Framework.
   Ein Test schlägt fehl, sobald wieder auswertbare Attribute im HTML landen.
3. **Umlaute überall.** Keine „Ueber uns"-Schreibweisen in sichtbaren Texten.
4. **Barrierefreiheit ist der Kern des Auftrags**, nicht ein Feature.

---

## Was in der letzten Session entstanden ist

Vier Commits, alle Tests grün. Kurzfassung:

- **Sprachen als Tabelle** (`languages`) mit Filament-Resource. `richtung` (ltr/rtl)
  von Anfang an mitgeführt. Der Verein kann selbst Sprachen anlegen.
- **Übersetzungen als eigene Seiten-Datensätze** — `pages.locale` +
  `pages.uebersetzungs_gruppe`, Unique-Index von `slug` auf `(locale, slug)`.
- **URLs mit Präfix, Deutsch ohne.** Die 24 Adressen und 26 Weiterleitungen sind
  unverändert. `/de/…` leitet dauerhaft auf die präfixlose Adresse um.
- **Sprachumschalter, hreflang, sitemap.xml, eigene Fehlerseiten (404/500/503).**
- **Literata** als kyrillische Displayschrift (Fraunces kann kein Kyrillisch).
- **axe-core in der Testsuite** — fand neun echte Verstösse, alle behoben.
- **Übersetzungen im Filament anlegbar** (Sprache + „Übersetzung von").

Die Begründungen stehen in `docs/Komponenten.md`, Abschnitt „Mehrsprachigkeit".
**Lies dort mindestens „Die Falle, die dabei fast aufgegangen wäre" und die beiden
Abschnitte „Behobener Fehler"** — beides sind Dinge, die man sonst wieder einbaut.

---

## Auftrag A — Mehrsprachigkeit fertigstellen

Das Gerüst steht. Was fehlt, ist Fläche.

### A1 · Die übrigen Inhaltstypen übersetzbar machen

`posts`, `categories`, `events`, `groups`, `team_members` haben noch kein `locale`
und keine Übersetzungsgruppe. Das Muster von `pages` lässt sich übertragen —
Migration, Model-Scopes, Filament-Felder, Controller-Rückfall.

Achtung bei `events`: Termine sind sprachunabhängig *terminiert*, aber
sprachabhängig *beschrieben*. Prüf, ob eine Übersetzung wirklich ein eigener
Datensatz sein soll oder ob hier ausnahmsweise Felder je Sprache besser sind —
ein Termin, den jemand nur auf Englisch pflegt, darf im deutschen Kalender nicht
verschwinden. Das ist der eine Fall, wo ich von der Seiten-Lösung abweichen würde.

**Nicht** übersetzbar: `inquiries` (Nutzereingaben), `redirects`, `users`.

### A2 · Die restlichen fest verdrahteten Texte

Eine Bestandsaufnahme hat **291 sichtbare Strings** in `resources/views/` und den
Configs gezählt — die Zahl „rund 40" aus dem alten Auftrag war deutlich zu niedrig.
Übersetzt ist bisher die **Rahmen-Oberfläche** (Kopf, Fuss, Notausgang, Brotkrumen,
Mobil-Leiste, Fehlerseiten) in `lang/{de,en,ru}/rahmen.php` und `navigation.php`.

Offen, nach Nutzen sortiert:

1. **`components/blocks/contact-form.blade.php`** (20 Strings) — das Kontaktformular
   ist der wichtigste unübersetzte Ort. Wer eine Anfrage stellen will, muss das
   Formular verstehen. Dazu die Fehlermeldungen in `app/Http/Requests/AnfrageRequest.php`.
2. **`config/darstellung.php`** (25 Strings) — die Barrierefreiheits-Toolbar.
   **Achtung:** Configs werden per `config:cache` eingefroren, `__()` darin ist
   unzuverlässig. Übersetzungsschlüssel statt Klartext eintragen und im Blade auflösen.
3. **`components/blocks/*`** — `embed` (8), `download-list` (5), `group-list` (4),
   `hilfe-box` (4), `inhalts-hinweis` (4), `donation-options` (12).
4. **`config/hilfe.php`** (16 Strings) — hängt an der Notfallnummern-Frage, siehe A4.
5. **`home.blade.php`** (39 Strings) und `page.blade.php` — das ist **Vereinsinhalt**,
   kein UI. Nicht in `lang/` schieben, sondern abwarten: Diese Texte sollen laut
   Datei-Kommentar ohnehin nach `pages`/`page_blocks` wandern. Dann sind sie über
   die normale Übersetzung erledigt.
6. Deutsche Datums- und Zahlformate: `format('d.m.Y')` in `blog/index:117` und
   `blog/show:87`, `->locale('de')` in `events/index:106,128,187`, `number_format`
   in `download-list:10-11`. Alle vier auf die aktive Sprache umstellen.
7. `Group::TYPEN` und `Group::STATUS` liefern deutsche Labels aus dem Model ins Template.

### A3 · Nur 8 von 20 Baustein-Typen haben Felder im Filament

Das ist der grösste Einzelposten und **kein Mehrsprachigkeits-Problem** — es gilt
schon auf Deutsch. Für `hero`, `quick_access`, `topic_list`, `cta_band`,
`contact_close`, `contact_form`, `donation_options`, `embed`, `hilfe_box`,
`inhalts_hinweis`, `leichte_sprache`, `stat_strip`, `team_grid` und `group_list`
gibt es ausser `typ` und `data.titel` kein einziges Eingabefeld; sie sind nur per
Seeder befüllbar.

Solange das so ist, kann der Verein diese Bausteine weder auf Deutsch noch in einer
Übersetzung pflegen. „Alle pflegbaren Texte sind im Panel je Sprache pflegbar" ist
damit **nicht eingelöst**. Das gehört vor die Abnahme.

### A4 · Zwei Fragen, die vor dem Weiterbauen geklärt sein sollten

- **Notfallnummern je Sprache.** `config/hilfe.php` enthält deutsche Nummern. Was
  zeigen wir russisch- oder englischsprachigen Besuchern? Das „Hilfetelefon Gewalt
  gegen Frauen" (116 016) nennt selbst 18 Sprachen — das gehört geprüft. Steht als
  A6 auf der Übergabe-Checkliste. **Nichts davon selbst recherchieren und einsetzen.**
- **Leichte Sprache.** Derzeit ein Baustein-Typ, keine Sprache. Meine Empfehlung
  war und ist: fürs Erste trennen. Die `languages`-Tabelle kann `de-x-leicht` aber
  ohne Migration aufnehmen, falls Kevin das anders will. **Noch nicht entschieden.**

### A5 · Kleinere offene Enden

- **RTL ist vorbereitet, aber nicht durchgezogen.** `richtung` steht in der Tabelle,
  `dir` am `<html>` und an Rückfall-Inhaltsbereichen. Ein vollständiger RTL-Durchgang
  der CSS (logische Eigenschaften statt `left`/`right`) fehlt und ist erst nötig,
  wenn wirklich Arabisch oder Farsi dazukommt.
- **`lang/en/` und `lang/ru/` sind ungeprüft.** Beide Sprachen stehen deshalb auf
  `aktiv = false` und erscheinen in keinem Umschalter. Erst nach muttersprachlichem
  Gegenlesen freischalten.
- **Der Blog kennt noch keine Sprachfilterung.** `/ru/aktuelles` zeigt aktuell alle
  Beiträge. Fällt mit A1 zusammen.

---

## Auftrag B — Restliche offene Punkte

**Jetzt sinnvoll:**

1. **CI einrichten.** Es gibt keine. Drei Testläufe (`php artisan test`,
   `npm run test:browser`, `npm run test:a11y`) laufen nur, wenn jemand daran denkt.
   Der axe-Lauf braucht einen laufenden Server — das ist der einzige Stolperstein.
2. **Manueller Barrierefreiheits-Durchgang.** axe-core findet 30–50 % der Verstösse.
   Screenreader-Test (NVDA oder VoiceOver), Tastatur-Durchlauf, und einmal mit
   400 % Zoom durch die wichtigsten Seiten.
3. **`docs/Uebergabe-Checkliste.md` Abschnitt B1 abarbeiten** — `APP_KEY` sichern,
   `SESSION_SECURE_COOKIE`, `/module-demo` entfernen, `MAIL_ANFRAGEN_AN` setzen.

**Bewusst liegen lassen:**

- **Die ~120 PDFs.** 32 Dokument-Links in 8 Bausteinen zeigen auf
  `/wp-content/uploads/…` und laufen lokal ins Leere. Das ist Meilenstein 10 und
  wartet auf die Anzahlung. **Ohne diesen Schritt ist die Seite nicht live-fähig** —
  bitte nicht übersehen, nur weil sonst alles fertig aussieht.
- **Datenschutzerklärung.** Die übernommene beschreibt OneTap, hu-manity und Google
  Fonts — nichts davon setzen wir ein. Gehört zu den Anwälten des Vereins.
  **Sie muss um die Mehrsprachigkeit ergänzt werden**, sobald sie neu geschrieben ist:
  Sprachwahl wird nicht gespeichert, es gibt keinen Sprach-Cookie, keine
  IP-Auswertung zur Spracherkennung. Das ist eine bewusste Entscheidung und
  erwähnenswert.
- **Mitgliederbereich mit Login.** Kevins Entscheidung: später, Schema vorbereitet.

---

## Kaufmännisch — bitte weiter ansprechen

Mehrsprachigkeit steht **in keiner Position von AN-268** (9.726,55 € pauschal).
Das technische Grundgerüst ist gebaut; A1 bis A3 sind der grössere Teil und
laufender Pflegeaufwand kommt danach. Wurde in der letzten Session angesprochen
und steht als C1 auf der Übergabe-Checkliste — **noch nicht mit Tatjana geklärt.**

Ebenfalls neu auf der Liste: Die Desktop-Navigation ist jetzt ab 1280 px statt ab
1024 px sichtbar (darunter Burger-Menü). Das ist eine sichtbare Designänderung.
Begründung in `docs/Komponenten.md` — bei 1024 px passte die Reihe schon vorher
nicht und brach still um. Sollte Tatjana wissen, bevor sie es selbst bemerkt.

---

## Arbeitsweise

Deutsch, direkt, per „du". Erst verstehen, dann handeln. Prüfen statt raten —
Kevin arbeitet auf **Nobara/Fedora mit PHP 8.4**, getestet wird oft in einem
Container; jede Umgebungsannahme ist schon einmal schiefgegangen. Alles, was
auffällt, gehört in `bin/start` oder in einen Test, nicht ins Gedächtnis.
Am Ende committen und in einem Satz sagen, was offen blieb.
