# Auftrag für die nächste Session

Kopiervorlage. Stand: 27.07.2026, Commit `9d94f23`.

---

## Kontext

Du arbeitest an `/workspace/kein-einzelfall` — dem Laravel-Relaunch der Website von
**KE!N EINZELFALL e.V.** (Opferhilfe-Verein, Hamburg). Auftraggeberin ist Tatjana Belmar,
Umsetzung Nils-Digital (Kevin). Lies zuerst:

- `docs/Projektplan_KEIN-EINZELFALL_Relaunch.md` — Auftrag, Stack, Meilensteine, Risiken
- `docs/Komponenten.md` — was gebaut ist und warum, inkl. der behobenen Fehler
- `docs/Uebergabe-Checkliste.md` — offene Punkte für Kevin und den Verein
- `README.md` — Start, Tests, Voraussetzungen

**Stand:** Meilensteine 1–9 sind fertig. 24 Seiten, 10 Gruppen, 26 Weiterleitungen,
142 Tests / 1024 Assertions. Starten mit `bin/start`, testen mit `php artisan test`
und `npm run test:browser`.

**Vier Rahmenbedingungen, die nicht verhandelbar sind:**

1. **Inhalte werden nicht erfunden.** Der komplette Bestand der Altseite wird übernommen.
   `ContentTreueTest` prüft das in beide Richtungen.
2. **Die CSP erlaubt kein `'unsafe-eval'`.** Deshalb gibt es kein JavaScript-Framework —
   Alpine wertet Attributinhalte über `new Function()` aus und war dadurch lautlos
   funktionslos. Details in `docs/Komponenten.md`, Abschnitt „Behobener Fehler".
   Ein Test schlägt fehl, sobald wieder auswertbare Attribute im HTML landen.
3. **Umlaute überall.** Keine „Ueber uns"-Schreibweisen in sichtbaren Texten.
4. **Barrierefreiheit ist der Kern des Auftrags**, nicht ein Feature. Zielgruppe sind
   Menschen nach Straftaten, oft mobil und in akuten Situationen.

---

## Auftrag A — Mehrsprachigkeit (Schwerpunkt)

**Ziel:** Deutsch, Englisch, Russisch. Weitere Sprachen muss der Verein **im Filament
selbst anlegen** können. Alle pflegbaren Texte sollen dort je Sprache pflegbar sein.

### Zwei verifizierte Fallstricke — bitte vorab einplanen

**1. Die Hausschrift kann kein Kyrillisch.**

Geprüft am 27.07.2026 gegen die Google-Fonts-API:

| Schrift | Rolle | Subsets |
|---|---|---|
| **Fraunces** | Überschriften | vietnamese, latin-ext, latin — **kein Kyrillisch** |
| Source Serif 4 | Fliesstext | cyrillic-ext, cyrillic, greek, vietnamese, latin-ext, latin ✅ |
| Caveat | Akzente | cyrillic-ext, cyrillic, latin-ext, latin ✅ |

Auf Russisch fielen also **alle Überschriften auf eine Systemschrift zurück**. In
`public/fonts/` liegen ohnehin nur `-latin` und `-latin-ext`. Zu klären:
Ersatz-Displayschrift für Kyrillisch suchen (muss zur Anmutung von Fraunces passen)
und die kyrillischen Subsets nachladen. Es gibt dafür einen Skill
`google-fonts-self-hosten`. Achtung: beim Nachladen nach URL deduplizieren, nicht
nach Schriftschnitt — daran ist der Download schon einmal gescheitert.

**2. Rund 40 sichtbare Texte stehen fest in den Blades.**

Betroffen sind u.a. `layouts/app.blade.php`, `components/layout/*` (Notausgang,
Mobil-Leiste, Sprunglink), `blog/index.blade.php`, `home.blade.php`. Dazu kommen die
Beschriftungen der Barrierefreiheits-Toolbar, die seit dem letzten Commit in
`config/darstellung.php` liegen. `lang/de` und `lang/en` existieren bisher nur mit
Framework- und Filament-Strings.

### Architektur — Empfehlung, bitte prüfen und begründet abweichen

**Sprachen als Datenbanktabelle, nicht als Config.** `languages`: `code`, `label`
(Eigenbezeichnung, z.B. „Русский"), `richtung` (`ltr`/`rtl`), `aktiv`, `position`,
`ist_standard`, `fallback_code`. Als Filament-Resource verwaltbar.
`richtung` von Anfang an mitführen, auch wenn DE/EN/RU alle `ltr` sind — für einen
Opferhilfeverein in Hamburg sind Arabisch oder Farsi realistische spätere Wünsche,
und nachträglich ist RTL teuer.

**Übersetzungen als eigene Seiten-Datensätze, nicht als JSON-Spalten.**
`pages` bekommt `locale` und eine `uebersetzungs_gruppe`. Jede Sprache ist eine
eigene Zeile mit eigenem Slug und eigenen Bausteinen. Begründung:

- Eine Übersetzung darf eine **andere Baustein-Struktur** haben. Das ist bei diesem
  Projekt kein Randfall: Die Zielgruppen-Module (`leichte_sprache`, `hilfe_box`,
  `inhalts_hinweis`) und die Notfallnummern sind sprach- und länderabhängig.
- Slugs müssen übersetzbar sein, sonst verschenkt man SEO.
- „Noch nicht übersetzt" ist damit ein natürlicher Zustand statt eines leeren Feldes.
- Der bestehende Renderer bleibt unangetastet.

Ein Paket wie `spatie/laravel-translatable` (JSON-Spalten pro Feld) passt hier
schlechter, weil `page_blocks.data` bereits JSON ist — das würde JSON in JSON.
Prüfe das trotzdem gegen, bevor du entscheidest.

**URLs mit Präfix, Deutsch ohne.** `/verein`, `/en/about`, `/ru/…`.
Das ist die wichtigste Einzelentscheidung: Nur so bleiben die 24 bestehenden URLs und
die 26 Weiterleitungen unverändert. „SEO darf nicht schlechter werden" ist
ausdrücklicher Kundenwunsch. Dazu `hreflang`-Auszeichnung, `canonical` je Sprache und
eine Sitemap mit `alternate`-Einträgen.

**Sprachumschalter** im Kopfbereich: normale Links, ohne JavaScript bedienbar, und er
darf den Notausgang weder verdecken noch verdrängen. Auf Mobil ist der Platz neben
Notausgang und Einstellungsknopf bereits knapp — schau dir das im Browser an, bevor du
es baust.

**Was übersetzbar wird:** `pages` + `page_blocks`, `posts`, `categories`, `events`,
`groups`, `team_members` (Rolle/Beschreibung), Navigation, UI-Strings.
**Nicht** übersetzbar: `inquiries` (Nutzereingaben), `redirects`, `users`.

### Inhaltliche Grenze — bitte ausdrücklich beachten

**Keine maschinelle Übersetzung der Inhalte.** Es geht um Rechtsberatung, Opferrechte,
Fristen und Notfallnummern. Eine unscharfe Übersetzung kann hier realen Schaden
anrichten. Wir bauen die Struktur und pflegen ein, was der Verein liefert — nichts
anderes. Leere Übersetzungen fallen sichtbar auf die Standardsprache zurück, mit
Hinweis, statt still falsch zu sein.

**Für Kevin zu klären, bevor gebaut wird:** Die Notfallnummern in `config/hilfe.php`
sind deutsche Nummern. Was zeigen wir russisch- oder englischsprachigen Besuchern?
Gibt es mehrsprachige Hotlines? Das gehört auf die Liste für Tatjana.

### Offene Frage an Kevin

**Leichte Sprache** ist derzeit ein Baustein-Typ, keine Sprache. Sie könnte auch als
Sprachvariante (`de-x-leicht`) in der `languages`-Tabelle geführt werden — dann bekäme
sie eine eigene URL und wäre verlinkbar. Meine Tendenz: fürs Erste trennen, aber die
Tabelle so bauen, dass es später möglich bleibt. Frag Kevin, bevor du dich festlegst.

---

## Auftrag B — Restliche offene Punkte

**Jetzt sinnvoll (unabhängig vom Fördergeld):**

1. **`sitemap.xml`** existiert nicht. `public/robots.txt` hat keine `Sitemap:`-Zeile.
   Die Übergabe-Checkliste verweist bereits auf eine Sitemap, die es nie gab.
   Muss mehrsprachig gedacht werden — also am besten zusammen mit Auftrag A.
2. **Eigene 404-Seite.** `resources/views/errors/` ist leer. Für diese Zielgruppe sollte
   eine Sackgasse Auswege anbieten (Suche, Hilfe-Box, Notausgang), nicht nur „nicht
   gefunden".
3. **axe-core in die Testsuite.** Das ist der wichtigste Punkt. „Maximal barrierefrei"
   ist ohne Messung nicht abnahmefähig — Risiko 4 im Projektplan. WCAG 2.1 AA
   automatisiert prüfen, am besten angedockt an `tests/Browser/bedienung.mjs`
   (Playwright ist bereits eingerichtet). Eine CI gibt es noch nicht.
4. **Spam-Schutz vervollständigen.** Aktuell nur `throttle:5,10` auf `POST /anfrage`.
   Honeypot und Zeitfalle aus dem Projektplan fehlen. Kein reCAPTCHA — kein
   Drittanbieter bei Art.-9-Daten.

**Bewusst liegen lassen:**

- **Die ~120 PDFs.** 32 Dokument-Links in 8 Bausteinen zeigen auf
  `/wp-content/uploads/…` und laufen lokal ins Leere. Das ist Meilenstein 10 und
  wartet auf die Anzahlung. **Ohne diesen Schritt ist die Seite nicht live-fähig** —
  bitte nicht übersehen, nur weil sonst alles fertig aussieht.
- **Datenschutzerklärung.** Die übernommene beschreibt OneTap, hu-manity und Google
  Fonts — nichts davon setzen wir ein. Gehört zu den Anwälten des Vereins, nicht zu uns.
- **Mitgliederbereich mit Login.** Kevins Entscheidung: später, Schema vorbereitet.

---

## Kaufmännischer Hinweis — bitte Kevin gegenüber ansprechen

Mehrsprachigkeit steht **in keiner Position von Angebot AN-268** (9.726,55 € pauschal).
Drei Sprachen bedeuten dreifachen Pflegeaufwand, dreifache Qualitätssicherung, eine
zusätzliche Displayschrift und einen erweiterten Barrierefreiheits-Durchlauf je Sprache.
Der Projektplan hält schon jetzt fest, dass das Budget für den Umfang knapp ist.

Das ist eine eigene Angebotsposition und sollte mit Tatjana geklärt sein, bevor gebaut
wird. Bau trotzdem los, wenn Kevin das sagt — aber sag es einmal deutlich.

---

## Arbeitsweise

Deutsch, direkt, per „du". Erst verstehen, dann handeln. Prüfen statt raten —
Kevin arbeitet auf **Nobara/Fedora mit PHP 8.4**, getestet wird oft in einem
Container; jede Umgebungsannahme ist schon einmal schiefgegangen. Alles, was auffällt,
gehört in `bin/start` oder in einen Test, nicht ins Gedächtnis.
Am Ende committen und in einem Satz sagen, was offen blieb.
