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
| `sprungmarken` | ✅ | Inhaltsverzeichnis, erscheint automatisch ab 4 Abschnitten. Anker leiten sich vom Titel ab, nicht von der ID — geteilte Links überleben ein Neu-Einpflegen |

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

## 4b. Seiten-System (27.07.2026)

Die 23 Inhaltsseiten liegen in der Datenbank und sind durchklickbar.

**Ablauf, bewusst zweistufig:**

```bash
php artisan altseite:holen              # Bestand → docs/altseite-inhalt.json
php artisan db:seed --class=AltseiteSeeder   # JSON → pages + page_blocks
```

Der Abzug ist getrennt vom Einpflegen, damit man ihn prüfen und am git-Diff
sehen kann, was der Verein zwischenzeitlich geändert hat.

**Zum Extrahieren:** Elementor markiert den Seiteninhalt mit
`data-elementor-type="wp-page"`, das Theme schließt mit `<!-- #page -->`.
Alles danach ist Footer und OneTap-Widget — letzteres allein bringt 42
Sprachlisten mit Flaggenbildern mit und würde jede Auswertung unbrauchbar machen.

### Weiterleitungen

| Fall | Beispiel |
|---|---|
| WordPress-Schrägstrich | `/verein/` → `/verein` (301) |
| Slug bereinigt | `/impressum-2` → `/impressum` |
| Auf der Altseite kaputt | `/selbsthilfegruppen-2` → `/selbsthilfegruppen`, `/kontaktformular` → `/anfragen` |

Die Schrägstrich-Umleitung ist kein Detail: die Altseite veröffentlicht
**ausschließlich** Adressen mit Schrägstrich, es betrifft also jede indexierte URL.
`redirects.treffer` zählt mit, welche Regel nach dem Go-Live tatsächlich greift.

> ⚠️ `php artisan serve` entfernt abschließende Schrägstriche selbst, bevor Laravel
> sie sieht — dort lässt sich das Verhalten nicht prüfen. Unter Apache/nginx kommt
> der Pfad unverändert an. Deshalb testet `SeitenUndRedirectsTest` die Middleware
> direkt; auch `$this->get('/verein/')` trimmt sonst schon in `prepareUrlForRequest()`.

## 5. Admin (Filament 4)

Erreichbar unter `/admin`. Oberfläche auf Deutsch, Hausfarbe `#2E4A3A`.

**Zugang ist ausdrücklich, nicht stillschweigend.** `users.panel_zugang` steht
standardmäßig auf „nein"; `User::canAccessPanel()` prüft genau dieses Kennzeichen.
Das ist keine Formsache: Sobald Vereinsmitglieder eigene Konten bekommen, liegen
sie in derselben Tabelle wie die Redaktion — ohne den Riegel käme jedes
angemeldete Mitglied an die Anfragen und damit an Art.-9-Daten.
`User::factory()->redaktion()` erzeugt ein freigeschaltetes Konto; ein normal
erzeugtes Konto darf bewusst **nicht** ins Panel, damit fehlende
Berechtigungsprüfungen in Tests auffallen.

| Bereich | Status |
|---|---|
| Seiten + Block-Editor | ✅ Bausteine per Ziehen sortierbar, Typ-Auswahl aus `PageBlock::TYPEN`, Direktlink „Ansehen" auf die echte Seite |
| Weiterleitungen | ✅ inkl. Trefferzähler — nach dem Go-Live die wichtigste Spalte: eine Regel mit 0 Aufrufen ist überflüssig oder falsch geschrieben |
| Anfragen | ✅ Zähler offener Anfragen in der Navigation, Inhalte schreibgeschützt, Status setzt automatisch den Abschlusszeitpunkt (= Beginn der Aufbewahrungsfrist) |

## 6. Kontaktformular und Anfragen (27.07.2026)

Angebotsposition 4. Der Teil mit dem größten DSGVO-Gewicht — Menschen schreiben
hier über erlebte Straftaten und ihre Gesundheit (Art. 9 DSGVO).

**Getroffene Entscheidungen:**

| Entscheidung | Warum |
|---|---|
| Name und E-Mail **freiwillig** | Auf der Altseite sind beide Pflicht. Anonyme Kontaktaufnahme ist bei dieser Zielgruppe ein echtes Bedürfnis. Die Rückmeldung sagt ehrlich, dass ohne Adresse keine Antwort möglich ist |
| Felder **verschlüsselt** (`encrypted` Cast) | Ein Datenbankabzug — Backup, Hoster-Panel, offenes phpMyAdmin — zeigt keinen Klartext. Preis: kein `WHERE`, `LIKE` oder `ORDER BY` auf diesen Feldern |
| Benachrichtigung **ohne Inhalt** | E-Mail ist unverschlüsselt und bleibt jahrelang in Postfächern. Der Hinweis enthält nur Eingangszeit und einen Link ins Panel. **Der wirksamste einzelne Hebel im ganzen Projekt** |
| **Keine IP-Adresse**, kein User-Agent | Was nicht gespeichert wird, kann nicht abfließen |
| Honigtopf + Zeitfalle statt CAPTCHA | Ein CAPTCHA wäre eine zusätzliche Hürde ausgerechnet für Menschen, die ohnehin Mühe haben. Auch kein reCAPTCHA — kein Drittdienst |
| Formular **ohne JavaScript** nutzbar | Muss auch in gehärteten Browsern und über Tor funktionieren |

**Löschkonzept:** `php artisan anfragen:aufraeumen`, täglich um 3:30 Uhr
(`routes/console.php`). Erledigte Anfragen 90 Tage nach Abschluss, unbearbeitete
365 Tage nach Eingang — bewusst länger, damit niemandem die Nachricht gelöscht
wird, bevor sie überhaupt jemand gelesen hat. `--probe` zeigt an, ohne zu löschen.

> ⚠️ **Vor dem Go-Live zu klären:** Die Fristen in `config/anfragen.php` sind ein
> Vorschlag, kein Rechtsrat. Der Verein stellt dafür eigene Anwälte zur Verfügung.
> Zu klären ist insbesondere, ob Anfragen mit Bezug zu laufenden Verfahren länger
> aufbewahrt werden müssen — dann braucht es dafür ein ausdrückliches Kennzeichen
> statt einer pauschal längeren Frist.

> ⚠️ **`APP_KEY` gehört in die Sicherung.** Die Verschlüsselung hängt daran.
> Geht der Schlüssel verloren, sind alle Anfragen unwiederbringlich weg.

## 7. Sicherheits-Header und Einbettungen (27.07.2026)

### Kein Zustimmungsbanner nötig — und das ist ein Ergebnis, keine Nachlässigkeit

Ein Banner ist nur dann Pflicht, wenn nicht notwendige Cookies gesetzt oder
Fremdinhalte ungefragt geladen werden. Gemessen wird auf dieser Seite gesetzt:

| Cookie | Zweck | Einordnung |
|---|---|---|
| `XSRF-TOKEN` | Schutz vor Fremdanfragen beim Formular | technisch notwendig |
| `kein-einzelfall-session` | Sitzung, Formular-Fehlermeldungen | technisch notwendig |

Kein Tracking, keine Analyse, keine Fremdinhalte ohne Zustimmung. Damit greift
§ 25 Abs. 2 TDDDG und es braucht keine Einwilligung. Der Test
`test_seite_setzt_nur_technisch_notwendige_cookies` schlägt an, sobald das
jemals nicht mehr stimmt.

Zum Vergleich: Die Altseite **hat** einen Banner (`hu-manity.co`) — konfiguriert
mit `"blocking":false`, er blockiert also nichts, und die betterplace-Rahmen
laden trotzdem ungefragt. Ein Banner, der nichts verhindert, ist schlechter als
keiner: Er suggeriert Kontrolle, die es nicht gibt.

### Header

Gesetzt von `App\Http\Middleware\SicherheitsHeader` — die Altseite liefert
**keinen einzigen** davon aus.

| Header | Wert |
|---|---|
| `Content-Security-Policy` | siehe unten |
| `X-Content-Type-Options` | `nosniff` |
| `X-Frame-Options` | `DENY` |
| `Referrer-Policy` | `no-referrer` — wichtig für den Notausgang: die Zielseite erfährt die Herkunft nicht |
| `Permissions-Policy` | Kamera, Mikrofon, Standort, Zahlung, USB aus |
| `Strict-Transport-Security` | nur über HTTPS gesendet |

**Zur CSP:** Skripte laufen über ein **Nonce pro Antwort**, nicht über
`'unsafe-inline'` — das würde den Schutz weitgehend aufheben. Betroffen sind die
beiden notwendigen Inline-Skripte (Notausgang, Darstellungs-Einstellungen)
**und** die von Vite erzeugten Tags: `'strict-dynamic'` setzt `'self'` ausser
Kraft, ein Tag ohne Nonce würde blockiert. Dafür sorgt `Vite::useCspNonce()`.

Bei Stilen bleibt `'unsafe-inline'` vorerst nötig — Alpine setzt `style`-Attribute
und die Darstellungs-Einstellungen schreiben Custom Properties auf `<html>`.

### Zwei-Klick-Einbettung

`x-blocks.embed`. Der Rahmen steckt in einem `<template>` und existiert vor der
Zustimmung **nicht im Dokument** — es geht also kein einziger Aufruf an den
Anbieter. Beim Zuklappen wird er wieder entfernt, damit im Hintergrund nichts
weiterläuft. Als natives `<details>` umgesetzt: ohne JavaScript bedienbar.

Zugelassene Anbieter stehen in `config/embeds.php` und fliessen in `frame-src`
ein. Was dort nicht steht, lädt nicht — auch dann nicht, wenn jemand
versehentlich einen Einbettungscode in einen Textbaustein kopiert.

### Spendenseite

`x-blocks.donation-options` mit den echten Angaben: Überweisung (Deutsche
Skatbank), PayPal als reiner Link (kein eingebettetes Skript — solange niemand
klickt, erfährt PayPal nichts von diesem Besuch), die beiden betterplace-Projekte
als Zwei-Klick-Einbettung, und die Spendenbescheinigung per E-Mail.
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
