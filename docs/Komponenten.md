# Komponenten- und Modul-Inventar

Das Mockup zeigt **eine** Seite und definiert damit Farbwelt, Typografie und Tonalität —
nicht den Funktionsumfang. Diese Liste leitet die tatsächlich benötigten Module aus den
24 Bestandsseiten der Altseite ab.

**Prinzip:** kuratierte Block-Typen statt freier Page-Builder. Jeder Block ist eine
Blade-Komponente, die es genau einmal gibt, und ein Eintrag in `page_blocks.type`.
Redaktion setzt Seiten aus Blöcken zusammen, kann aber kein Layout zerschießen —
genau der Unterschied zu Elementor auf der Altseite.

Status: ✅ gebaut · 🔨 geplant · 💭 zu klären

---

## 1. Fundament (seitenübergreifend)

| Komponente | Status | Notiz |
|---|---|---|
| `layouts/app` | ✅ | Skip-Link, `<main>`, `lang="de"` |
| `layout/header` | ✅ | Serverseitige Nav mit Dropdowns (Tastatur ohne JS), Mobile-Menü |
| `layout/footer` | ✅ | 4 Spalten, Nils-Digital-Nennung |
| `layout/mobile-bar` | ✅ | Sticky, Safe-Area, Notausgang |
| `layout/exit-button` + `exit-script` | ✅ | Echtes `<a>`, 3×ESC, ohne JS nutzbar |
| `layout/a11y-toolbar` | ✅ | 11 Einstellungen, localStorage, kein FOUC |
| `ui/button` | ✅ | 4 Varianten, `<a>` vs `<button>` je nach Zweck |
| `ui/icon` | ✅ | Inline-SVG, `aria-hidden` |
| Cookie-Consent | 🔨 | Opt-in, blockierend — Altseite hat `blocking:false` |
| Breadcrumb | 🔨 | + JSON-LD `BreadcrumbList` |
| Pagination | 🔨 | Blog, Events; GET-Parameter, kein Livewire |
| 404 / 500 | 🔨 | Mit Notausgang und Suchhilfe |

## 2. Inhaltsblöcke aus dem Mockup

| Block | Status | Verwendet auf |
|---|---|---|
| `hero` | 🔨 | Startseite |
| `stat_strip` | 🔨 | Startseite |
| `quick_access` | 🔨 | Startseite |
| `text_media` | 🔨 | „Wer wir sind" |
| `topic_list` | 🔨 | Wissen |
| `event_teaser` | 🔨 | Startseite |
| `news_teaser` | 🔨 | Startseite |
| `cta_band` | 🔨 | Startseite |
| `contact_close` | 🔨 | Startseite, Kontakt |

## 3. Module, die das Mockup **nicht** zeigt

Abgeleitet aus dem tatsächlichen Bestand:

| Block | Für welche Seiten | Warum |
|---|---|---|
| `text` (Prosa) | fast alle | Basisblock. Überschriften, Listen, Zitate |
| `download_list` | Erwerbsminderungsrente (12 PDFs), FSM (10), Mitgliedschaft (3), Satzung, Kinderkodex | **Größter Migrationsposten: ~120 PDFs.** Braucht Dateigröße, Typ, Datum |
| `team_grid` | Über uns – Vorstand und Team | 2.441 Wörter, 7 Porträts. Eigene Entity `team_members` |
| `accordion` / FAQ | Bürokratie-Labyrinth, Hilfesystem | + JSON-LD `FAQPage` |
| `group_list` | Selbsthilfegruppen, Arbeitsgruppen | Aus `groups`, mit Terminen und Anmeldestatus |
| `event_list` / Kalender | Veranstaltungen | Eigenbau (Position 2). Listen- und Monatsansicht, iCal |
| `donation_options` | Spenden | IBAN + PayPal + betterplace. **betterplace nur als 2-Klick** |
| `contact_form` | Kontakt, Anfragen | Art.-9-Daten, verschlüsselt, anonym möglich |
| `legal_text` | Datenschutz (2.243 W.), Impressum, Satzung | Lange Prosa + Sprungmarken-Inhaltsverzeichnis |
| `embed` | ggf. Videos | 2-Klick-Lösung, nie direkt laden |

## 4. Module speziell für diese Zielgruppe

Am 26.07.2026 beauftragt und gebaut.

| Block | Status | Zweck |
|---|---|---|
| `hilfe_box` | ✅ | Notfallnummern als `tel:`-Links. Nummern in `config/hilfe.php`, damit sie überall identisch und pflegbar sind. Variante `:kompakt` für Randspalten |
| `inhalts_hinweis` | ✅ | Vorwarnung vor belastenden Inhalten. Natives `<details>` — ohne JS bedienbar, Screenreader kennen das Muster, Inhalt bleibt indexierbar |
| `leichte_sprache` | ✅ | Zusammenfassung nach WCAG AAA (3.1.5). Steht oben auf der Seite, nicht unten. Typografie: größere Schrift, Zeilenabstand 2, Flattersatz, max. 55 Zeichen |
| `download_list` | ✅ | Titel statt Dateiname, Typ + Größe vorab (WCAG 2.4.4 / 3.2.5), kein `target="_blank"` |
| `sprungmarken` | 🔨 | Inhaltsverzeichnis für lange Seiten |

**Noch zu klären:**
- Notfallnummern in `config/hilfe.php` vom Verein gegenprüfen lassen (Zuständigkeiten ändern sich)
- Texte für Leichte Sprache schreibt der Verein. Das ist eine eigene Disziplin mit
  Regelwerk und gehört idealerweise von einer Prüfgruppe aus der Zielgruppe abgenommen
- Auf welchen Seiten soll der `inhalts_hinweis` stehen? Vorschlag: Trauma/Bindung,
  Traumafolgestörungen, FSM

## 4a. Dokumente / PDF-Migration

**Bestandsaufnahme 26.07.2026.** Der komplette Medienbestand ist gesichert —
**121 Dokumente, 26,3 MB**, ohne dass wir Zugangsdaten gebraucht hätten: die
WordPress-REST-API (`/wp-json/wp/v2/media`) ist öffentlich.

| | Anzahl | |
|---|---|---|
| auf Seiten verlinkt | 31 | Titel aus dem Linktext der Altseite |
| Dubletten | 10 | WordPress-`-1`-Varianten derselben Datei |
| **unverlinkt, eigenständig** | **79** | im Web nirgends auffindbar |

Dateien unter `storage/app/migration/dokumente/` (gitignored), Inventar
versioniert in `docs/dokumente-manifest.json`.

### Die 79 unverlinkten Dokumente sind kein Müll

Es sind vollständige, systematisch nummerierte Infoblatt-Reihen:

| Reihe | Anz. | Thema |
|---|---|---|
| `6.5.1.x` | 27 | Atteste, Alltagsbeeinträchtigungen |
| `6.5.5.x` | 25 | Grad der Behinderung (Antrag, Kündigungsschutz, Krankengeld, Reha …) |
| `6.5.7.x` | 5 | Kfz-Hilfe |
| `6.1.3.x` | 5 | Einverständnis-/Datenschutzformulare |
| `6.2.2/6.2.3` | 4 | Behörden-Schriftwechsel (BMAS, BMG) |
| weitere | 13 | Erster Kontakt, Jugendamt, Stellungnahmen |

Der Verein hat also **erheblich mehr Material erarbeitet, als die Website zeigt** —
auf der Altseite ist es schlicht nicht auffindbar. Für den neuen Wissensbereich ist
das der größte inhaltliche Hebel im Projekt.

**Vor Veröffentlichung mit dem Verein klären:**
- Welche Reihen sollen öffentlich sein? Manches sieht nach internen Formularen aus
  (Verschwiegenheitserklärung, „Einverständnis Daten/Akten speichern")
- ⚠️ **Datenschutz:** Diese Dateien sind *heute schon öffentlich abrufbar*, nur nicht
  verlinkt. Bei Behörden-Schriftwechseln und Stellungnahmen ist zu prüfen, ob darin
  personenbezogene Daten stehen. Nicht-Verlinken ist kein Zugriffsschutz
- ⚠️ Eine `.docx` im Bestand (`6.5.4.4.-KE-Stellungnahme-BBM.docx`). Word-Dateien
  gehören nicht ins Web: sie transportieren Metadaten und oft Änderungsverfolgung.
  Als PDF exportieren oder entfernen

> **Verlinken auf die Altseite reicht nicht.** Die Dateien liegen unter
> `kein-einzelfall.de/wp-content/uploads/…`. Sobald die Domain auf den neuen
> Server zeigt, existiert dieser Pfad nicht mehr und alle Links sind tot —
> darunter amtliche Formulare und Schriftwechsel, die extern verlinkt sein können.

Migrationsweg: Dateien übernehmen, unter sprechendem Pfad ausliefern, und jede
alte `/wp-content/uploads/…`-URL per 301 darauf mappen.

Gefunden beim Inventarisieren: Tippfehler im Linktext auf `/mitgliedschaft/`
(„Hlfe zum Ausfüllen") — beim Einpflegen korrigieren.

## 5. Admin (Filament)

| Bereich | Status |
|---|---|
| Seiten + Block-Editor | 🔨 |
| Blog, Kategorien | 🔨 |
| Events | 🔨 |
| Gruppen | 🔨 |
| Anfragen (Art. 9, verschlüsselt, Löschfristen) | 🔨 |
| Mitglieder (ohne Login, nachrüstbar) | 🔨 |
| Medien / PDFs | 🔨 |
| Navigation | 🔨 |
| Redirects | 🔨 |

---

## Offene Design-Fragen

- [ ] Wie tief soll die Redaktion Blöcke anordnen dürfen? Vorschlag: Reihenfolge ja,
      Farben/Abstände nein — sonst zerfällt das ruhige Erscheinungsbild
- [ ] `hilfe_box`, `inhalts_hinweis`, `leichte_sprache`: gewünscht? (mit Verein klären)
- [ ] Bildmaterial: das Mockup nutzt Platzhalter. Fotos stellt der Verein
- [ ] Logo als Vektor + helle Variante für dunkle Flächen
