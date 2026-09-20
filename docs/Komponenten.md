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
| `hero` | ✅ | Startseite — im Panel pflegbar, inkl. Linie unter dem markierten Titelteil |
| `stat_strip` | ✅ | im Panel pflegbar |
| `quick_access` | ✅ | Startseite — im Panel pflegbar |
| `text_media` | ✅ | „Wer wir sind" |
| `topic_list` | ✅ | Wissen — im Panel pflegbar |
| `event_teaser` | 🔨 | Startseite |
| `news_teaser` | 🔨 | Startseite |
| `cta_band` | ✅ | Startseite — im Panel pflegbar |
| `donation_options` | ✅ | Startseite (kompakt), Spenden — im Panel pflegbar |
| `contact_close` | ✅ | Startseite, Kontakt — im Panel pflegbar |

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
| `donation_options` | Spenden, Startseite (kompakt, seit KEV-10) | IBAN + Girocode + PayPal + betterplace. **betterplace nur als 2-Klick** |
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

`'unsafe-eval'` steht bewusst **nicht** in der Richtlinie. Das ist der Grund,
warum die Seite kein JavaScript-Framework benutzt: Alpine wertet den Inhalt von
`@click`, `x-show`, `x-text` … zur Laufzeit über `new Function()` aus, und genau
das blockiert die CSP. Siehe „Behobener Fehler" weiter unten.

Bei Stilen bleibt `'unsafe-inline'` vorerst nötig: Tailwind-Utilities und die
Darstellungs-Einstellungen schreiben Custom Properties auf `<html>`.

### Zwei-Klick-Einbettung

`x-blocks.embed`. Der Rahmen steckt in einem `<template>` und existiert vor der
Zustimmung **nicht im Dokument** — es geht also kein einziger Aufruf an den
Anbieter. Beim Zuklappen wird er wieder entfernt, damit im Hintergrund nichts
weiterläuft. Als natives `<details>` umgesetzt: ohne JavaScript bedienbar.

Zugelassene Anbieter stehen in `config/embeds.php` und fliessen in `frame-src`
ein. Was dort nicht steht, lädt nicht — auch dann nicht, wenn jemand
versehentlich einen Einbettungscode in einen Textbaustein kopiert.

## 8. Blog und Veranstaltungen (27.07.2026)

Angebotspositionen 2 und 3. Damit sind alle sieben Positionen technisch abgedeckt.

### Blog — `/aktuelles`

Die Altseite hat **null Beiträge**, der Blog wird also neu aufgebaut. Die drei
bestehenden Kategorien sind übernommen, `/vereins-news` leitet auf `/aktuelles`.

Suche, Kategoriefilter und Blättern laufen über **GET-Parameter**: jeder Stand hat
eine eigene teilbare Adresse und funktioniert ohne JavaScript. `withQueryString()`
sorgt dafür, dass Filter und Suchbegriff beim Blättern erhalten bleiben.

> **Warum LIKE statt Volltextindex?** Der MySQL-Index klingt naheliegender, hat hier
> aber drei Nachteile ohne Gegenwert: Er greift erst ab vier Zeichen (fände „OEG"
> also nicht — ausgerechnet eines der wichtigsten Kürzel dieser Seite), ignoriert
> Stoppwörter stillschweigend, und er sieht keine Daten aus offenen Transaktionen.
> Da Laravel jeden Test in eine Transaktion legt — auch mit `DatabaseTruncation`,
> das wurde geprüft — liesse sich die Suche gar nicht testen. Ungetestete Suche auf
> einer Seite, auf der Menschen nach Hilfe suchen, ist keine gute Idee.
> LIKE erzwingt einen Tabellendurchlauf; bei einigen hundert Beiträgen nicht messbar.

### Veranstaltungen — `/veranstaltungen`

Ersetzt „The Events Calendar". Dessen Umfang wird bewusst nicht nachgebaut: auf der
Altseite steht dort genau **ein** Eintrag und es gibt keine kommenden Termine.

- **Der Bestandstext bleibt.** `/veranstaltungen` war eine gepflegte Inhaltsseite
  (443 Wörter) — der Text steht jetzt als Einleitung über dem Kalender, statt
  verworfen zu werden.
- Kommend/vergangen umschaltbar. Massgeblich ist das **Ende**, nicht der Beginn:
  eine mehrtägige Veranstaltung gilt am zweiten Tag noch als laufend.
- **iCal-Export** unter `/veranstaltungen/kalender.ics` und je Termin — die Altseite
  bietet das unter `/events/?ical=1` an, die Möglichkeit sollte nicht verlorengehen.
  Sonderzeichen sind nach RFC 5545 maskiert, sonst zerfällt die Datei.
- **Keine Anmeldeverwaltung**, nur ein Link. Anmeldungen zu Selbsthilfegruppen sind
  Art.-9-Daten und gehören in die Gruppenverwaltung mit eigenem Konzept — nicht
  nebenbei in den Kalender.

### Strukturierte Daten

`Article` für Beiträge, `Event` für Termine — beides fehlt der Altseite komplett.
Damit können Suchmaschinen Termine direkt im Ergebnis anzeigen.

> Blade-Fallstrick: `@json()` mit mehrzeiligem Array bricht beim Kompilieren
> („Unclosed '['"). Deshalb wird das JSON in einem `@php`-Block vorbereitet.

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

## 9. Bausteine für Unterseiten (27.07.2026)

Vier zusätzliche Block-Typen, damit sich Unterseiten im Panel abwechslungsreich
zusammenstellen lassen statt nur aus Textblöcken zu bestehen.

| Baustein | Wofür | Besonderheit |
|---|---|---|
| `schritte` | Antragswege, Widerspruchsverfahren, „was passiert wann" | Als `<ol>` ausgezeichnet — Screenreader sagen „1 von 5" an. Sichtbare Ziffern sind `aria-hidden`, sonst käme die Nummer doppelt |
| `accordion` | Häufige Fragen | Natives `<details>`: ohne JavaScript bedienbar, Inhalt bleibt für Suchmaschinen sichtbar. Bringt **FAQ-Auszeichnung** mit — Fragen können direkt im Suchergebnis erscheinen |
| `hinweis` | Fristen, wichtige Ausnahmen | Drei Stufen (neutral / wichtig / Frist). Bewusst **zurückhaltend eingefärbt**: grelle Warnflächen erzeugen bei belasteten Menschen Druck |
| `text_media` | Text mit Bild | Zeigt eine Platzhalterfläche in der Farbwelt der Seite, solange kein Foto hinterlegt ist — die Seite sieht auch ohne Bildmaterial fertig aus |

Alle vier sind im Filament-Panel pflegbar; die Felder erscheinen je nach
gewähltem Baustein-Typ.

### Zwei Fehler, die dabei aufgefallen sind

**Dokumentenliste lief über die volle Fensterbreite.** Ihr fehlte der
Seitencontainer, dadurch fiel sie aus dem Satzspiegel. Jetzt im selben Rahmen
wie die Textbausteine und als abgesetzte Karte statt randloser Liste.

**Vier Seitentitel schrieben Umlaute aus** — „Ueber Uns Vorstand Und Team".
Wo die Altseite keine Überschrift ausweist, wurde der Titel aus dem Slug
abgeleitet, und Slugs kennen keine Umlaute. Jetzt gibt es in `AltseiteSeeder`
eine ausgeschriebene Liste; ein Test prüft alle Titel.

**Ausgabepuffer-Leck in `page.blade.php`.** `@section('description', null)`
öffnet einen Puffer, den mangels `@endsection` niemand schliesst. Fiel nur auf,
weil PHPUnit die Tests als „risky" markierte — hätte im Betrieb zu schwer
auffindbaren Fehlern führen können. Seiten ohne gepflegte Beschreibung
bekommen jetzt den Titel als Notbehelf.

## 10. Vorstand und Gruppen als eigene Daten (27.07.2026)

Zwei Bereiche, die vorher als Fliesstext auf Seiten standen, sind jetzt eigene
Datensätze — pflegbar im Panel, einheitlich dargestellt, an mehreren Stellen
einbindbar.

### `team_grid` — Vorstand und Team

Die Seite umfasste 2.441 Wörter: drei Personen mit **18 bis 19 Absätzen**
Selbstvorstellung, hintereinander weg. Jetzt: Kurzangaben sichtbar, der
ausführliche Text über natives `<details>` aufklappbar. Ohne JavaScript
bedienbar, und der Volltext bleibt für Suchmaschinen im Dokument.

Personen ohne Foto bekommen **Initialen**, kein Platzhalter-Gesicht — ein
Symbol, das eine Person darstellen soll, die es so nicht gibt, wäre unehrlich.

Die Zuordnung aus dem Fliesstext folgt dem Muster der Altseite: Überschrift
ohne Text = Rolle, nächste = Name, dann die Vorstellung. **Eine Stelle liegt
daneben** und ist ausdrücklich korrigiert: „Herr und Frau Unbekannt" ist ein
stellvertretendes Porträt für die Menschen im Hintergrund, kein
Vorstandsmitglied — die darüberstehende Überschrift „Gemeinsam KE!N EINZELFALL"
ist entsprechend keine Rollenbezeichnung.

### `group_list` — Selbsthilfe- und Arbeitsgruppen

10 Gruppen: 4 Selbsthilfegruppen (eine davon „in Planung"), 6 Arbeitsgruppen
mit Kürzeln. Termin, Rhythmus und Ort stehen als eigene Felder statt im
Fließtext.

Geplante und geschlossene Gruppen treten optisch zurück und bekommen **keinen
Anfrage-Knopf** — er würde Erwartungen wecken, die niemand einlösen kann.

> ⚠️ **Bewusst keine Anmeldeverwaltung.** Wer sich zu einer Selbsthilfegruppe
> anmeldet, offenbart damit eine Angabe nach Art. 9 DSGVO. Das braucht ein
> eigenes Konzept mit Löschfristen und Zugriffsregelung und gehört nicht
> nebenbei in eine Gruppenübersicht. Der Weg führt über das Kontaktformular,
> dessen Daten verschlüsselt liegen. Ein Test hält fest, dass auf den
> Gruppenseiten kein Formular steht.

### Wie die Seiten neu zusammengesetzt werden

`TeamUndGruppenSeeder` ersetzt genau die Textabschnitte, deren Überschrift
eine Gruppe oder Person benennt. **Einleitung, Teilnahmeregeln und Dokumente
bleiben** — sie gehören nicht in die Aufzählung, sondern drumherum.

Der Abgleich läuft über die Namen der angelegten Datensätze, mit Mindestlänge
gegen Fehltreffer: Ein kurzer Name wie „Team" käme sonst in halben
Überschriften vor.

## 11. Kalender, Weiterlesen, Ladeverhalten (27.07.2026)

### Gruppentermine im Kalender

Die regelmässigen Treffen standen bisher nur als Freitext auf den Gruppenseiten
(„Jeden 4. Mittwoch im Monat"). Wer wissen wollte, wann das nächste Treffen ist,
musste die Seite durchsuchen. Jetzt erscheinen sie unter `/veranstaltungen`
über den Einzelveranstaltungen — sie finden am häufigsten statt und werden am
häufigsten gesucht.

Dafür hat `groups` zusätzliche Felder: `wiederholung`, `wochentag` (ISO 1–7),
`woche_im_monat` (1–5, wobei 5 „der letzte" bedeutet), `beginn_zeit`,
`dauer_minuten`. Der **Freitext bleibt** — er ist die verbindliche Anzeige, die
strukturierten Felder nur die Grundlage der Berechnung. Passt ein Rhythmus nicht
ins Schema, stimmt der Text trotzdem; dann entfallen nur die berechneten Termine.

**Berechnet statt gespeichert.** Ein wöchentlicher Termin würde sonst unbegrenzt
Datensätze erzeugen, die jemand pflegen müsste. Fällt ein einzelnes Treffen aus,
gehört das als Einzelveranstaltung erfasst.

Im iCal-Export sind sie ebenfalls enthalten, mit datumsbezogener Kennung
(`UID:gruppe-3-20260826@…`) — sonst legte jeder erneute Import Dubletten an.

> Stolperstein: `Carbon::next()` zählt 0 = Sonntag bis 6 = Samstag, gespeichert
> ist ISO-8601 (1 = Montag). Ein deutscher Wochentagsname wirft dort eine
> Ausnahme.

### Weiterlesen bei langen Abschnitten

Textbausteine mit mehr als fünf Absätzen zeigen die ersten vier und klappen den
Rest ein. Natives `<details>`: ohne JavaScript bedienbar, und der Text bleibt im
Dokument — Suchmaschinen und die Seitensuche des Browsers finden ihn trotzdem.
Auf `/datenschutz` betrifft das vier Abschnitte.

### Ladeverhalten

Alle Bilder unterhalb des ersten Sichtbereichs laden verzögert
(`loading="lazy"`). **Nicht** verzögert wird das Beitragsbild im Blog — es steht
ganz oben, dort würde verzögertes Laden die Anzeige nur bremsen. Das Logo im
Kopfbereich ebenso.

### Behobener Fehler: Einstellungsknopf reagierte nicht

Dreimal gemeldet, zweimal falsch repariert. Die Ursache lag nicht dort, wo sie
zu vermuten war.

**Fehlversuch 1.** Ohne gebaute Assets griff `[x-cloak]` nicht, das Panel stand
offen und liess sich nicht schliessen. Gegenmassnahme: `style="display:none"`.

**Fehlversuch 2.** Dieser Inline-Stil kollidierte mit Alpines `x-show` — beide
verwalteten dieselbe Eigenschaft, der Knopf reagierte gar nicht mehr.
Gegenmassnahme: das HTML-Attribut `hidden` samt `:hidden`.

**Die eigentliche Ursache.** Der Knopf war nie funktionsfähig. Alpine wertet den
Inhalt seiner Attribute zur Laufzeit über `new Function()` aus; unsere CSP
erlaubt kein `'unsafe-eval'`, der Browser blockierte also jede einzelne
Auswertung. Betroffen war die gesamte Bedienoberfläche, nicht nur dieser Knopf —
am Desktop fiel nur er auf, weil das Mobil-Menü dort ausgeblendet ist. Im HTML
war nichts Auffälliges zu sehen und alle PHPUnit-Tests waren grün: Der Browser
meldet das ausschliesslich in der Entwicklerkonsole.

**Die Entscheidung.** Zur Wahl standen `'unsafe-eval'` in die CSP aufnehmen,
Alpines CSP-Build verwenden (der genau die dynamischen Ausdrücke verbietet, für
die Alpine hier überhaupt eingesetzt war) — oder Alpine aufgeben. Bei einer
Seite, auf der Menschen über erlebte Straftaten schreiben, ist das Aufweichen
der CSP der falsche Handel; und für zwei Bedienelemente lohnt kein Framework:

- Das **Mobil-Menü** ist jetzt ein natives `<details>`, wie alle anderen
  Aufklapper im Projekt. Es braucht überhaupt kein JavaScript mehr.
- Das **Einstellungs-Panel** wird serverseitig aus `config/darstellung.php`
  gerendert; ~60 Zeilen eigenes JavaScript verdrahten es über `data`-Attribute.
  Nebeneffekt: Die Beschriftungen stehen jetzt im ausgelieferten HTML. Vorher
  baute Alpine das Panel per `x-for` zusammen — ausgerechnet die
  Barrierefreiheits-Einstellungen existierten also vor dem Ausführen des
  Skripts gar nicht.

Das Bundle ist dadurch von rund 45 KB auf **1,6 KB** geschrumpft.

**Konsequenz für die Absicherung.** Dieser Fehler war für PHPUnit prinzipiell
unsichtbar: Das ausgelieferte HTML war korrekt, nur der Browser führte es nicht
aus. Dazugekommen sind deshalb zwei Prüfungen:

- `StartbarkeitTest` schlägt fehl, sobald im ausgelieferten HTML wieder ein
  Attribut auftaucht, dessen Inhalt zur Laufzeit ausgewertet werden müsste
  (`x-…`, `@…`, `:…`) — und ebenso, wenn `'unsafe-eval'` in der CSP landet.
- `npm run test:browser` (`tests/Browser/bedienung.mjs`) bedient die Seite in
  einem echten Chromium: mit und ohne JavaScript, mit Maus und nur mit der
  Tastatur, und wertet dabei die Browser-Konsole mit aus.

---

## Mehrsprachigkeit

### Warum Sprachen in der Datenbank stehen und nicht in einer Config

Der Verein soll weitere Sprachen selbst anlegen können, ohne dass jemand eine
Datei anfasst und neu ausrollt. Die Tabelle `languages` führt `code` (zugleich
das Adresspräfix), `label` (Eigenbezeichnung), `label_deutsch`, `richtung`,
`aktiv`, `position`, `ist_standard` und `fallback_code`.

`richtung` wird von Anfang an mitgeführt, obwohl DE/EN/RU alle `ltr` sind. Für
einen Opferhilfeverein in Hamburg sind Arabisch oder Farsi realistische spätere
Wünsche, und RTL nachträglich einzuziehen ist teuer. Das `dir`-Attribut steht
bereits am `<html>` und an jedem Inhaltsbereich mit abweichender Sprache; ein
vollständiger RTL-Durchgang der CSS steht noch aus und ist erst nötig, wenn
wirklich eine RTL-Sprache dazukommt.

### Warum Übersetzungen eigene Seiten sind und keine JSON-Spalten

`pages` hat `locale` und `uebersetzungs_gruppe`. Jede Sprachfassung ist eine
eigene Zeile mit eigenem Slug und eigenen Bausteinen. Gründe:

- Eine Übersetzung darf eine **andere Baustein-Struktur** haben. Das ist hier
  kein Randfall: Die Zielgruppen-Module (`leichte_sprache`, `hilfe_box`,
  `inhalts_hinweis`) und die Notfallnummern sind sprach- und länderabhängig.
- Slugs müssen übersetzbar sein, sonst verschenkt man SEO.
- „Noch nicht übersetzt" ist ein natürlicher Zustand statt eines leeren Feldes.
- Der Renderer bleibt unangetastet — `page_blocks` hängt an `page_id`.

Ein Paket mit JSON-Spalten pro Feld (`spatie/laravel-translatable`) hätte
bedeutet, JSON in JSON zu schachteln: `page_blocks.data` ist bereits JSON.

Der Unique-Index wanderte dabei von `slug` auf `(locale, slug)`. Ohne diesen
Schritt könnte `/en/kontakt` nicht neben `/kontakt` stehen.

### Warum Deutsch kein Präfix bekommt

`/verein`, `/en/about-us`, `/ru/…`. Das ist die wichtigste Einzelentscheidung:
Nur so bleiben die 24 bestehenden Adressen und die 26 Weiterleitungen
unverändert. „SEO darf nicht schlechter werden" ist ausdrücklicher Kundenwunsch.
`/de/verein` leitet dauerhaft (301) auf `/verein` um, damit kein Inhalt unter
zwei Adressen erreichbar ist.

Die öffentlichen Routen stehen **einmal** in einer Closure in `routes/web.php`
und werden **zweimal** registriert: präfixlos unter den bisherigen Namen und
mit Präfix unter `sprache.…`. Der Helfer `sprachlink()` wählt in den Views die
passende Variante. Ein einziger Satz Routen mit optionalem Präfix ging nicht:
Das erzeugt für die Standardsprache Adressen mit doppeltem Schrägstrich, und
`URL::defaults()` würde Deutsch ein Präfix verpassen.

Welche Sprachcodes gültig sind, entscheidet die **Datenbank in der Middleware**
und nicht das Routen-Muster. Stünde die Liste im Muster, bräuchte jede neue
Sprache ein `route:clear` — das ist niemandem zumutbar, der kein Terminal hat.

### Die Falle, die dabei fast aufgegangen wäre

Das Adressmuster für Sprachpräfixe steht im Routing **vor** der Sammelroute
`/{slug}`. Alles, was auf das Muster passt, wird als Sprache gelesen. Eine
deutsche Seite mit dem Slug `ru` — oder auch nur `ab-cd` — wäre damit
unerreichbar gewesen, ohne dass irgendetwas eine Fehlermeldung geworfen hätte.

Zwei Gegenmassnahmen:

1. Das Muster ist bewusst eng: zwei Buchstaben als Grundform, optionale
   Zusätze (`pt-br`, `de-x-leicht`). Drei Buchstaben wären auch üblich
   (ISO 639-3), würden aber Slugs wie `faq` blockieren — die sind
   wahrscheinlicher als eine Sprache ohne zweibuchstabige Kennung.
2. `App\Rules\KollidiertNichtMitSprachpraefix` lehnt beim Speichern in beide
   Richtungen ab: einen Seiten-Slug, der wie eine Sprachkennung aussieht, und
   einen Sprachcode, der eine bestehende Seitenadresse belegt.

Ein Test prüft zusätzlich den gesamten Bestand.

### Sichtbarer Rückfall statt 404

Fehlt eine Übersetzung, zeigt die Seite die Standardsprache — mit einem ruhigen
Hinweis in der gewählten Sprache. Nicht 404: Es geht um Opferrechte, Fristen
und Notfallnummern. Eine Seite, die still verschwindet, ist für diese Zielgruppe
schlechter als eine Seite in einer anderen Sprache mit dem Hinweis, dass sie
noch nicht übersetzt ist.

Der Inhaltsbereich trägt dann `lang="de"` (WCAG 3.1.2). Das ist nicht
kosmetisch: Ohne diese Auszeichnung spräche eine Vorlesehilfe den deutschen Text
mit russischer Aussprache aus.

### Fraunces kann kein Kyrillisch

> **Stand 19.09.2026: erledigt durch Wegfall.** Russisch ist gestrichen
> (Abschnitt 21), die kyrillischen Schnitte und Literata sind aus dem Projekt
> raus. Der Abschnitt bleibt als Befund stehen — für die nächste Sprache mit
> anderem Alphabet gilt er wieder.

Geprüft gegen die Google-Fonts-API: Fraunces liefert `latin`, `latin-ext`,
`vietnamese`. Auf Russisch wären also **alle** Überschriften auf eine
Systemschrift zurückgefallen.

Ersatz ist **Literata**, ausgewählt aus vier Kandidaten (Literata, Vollkorn,
Alegreya, Playfair Display), nebeneinander gerendert und verglichen: Sie kommt
Gewicht und Wärme von Fraunces am nächsten. Playfair Display wäre
kontrastreicher, aber Haarstriche sind für sehbeeinträchtigte Leser die
schlechtere Wahl — und genau die sind hier Zielgruppe.

Literata steht **hinter** Fraunces im Stapel, nicht statt ihr: Fraunces deckt
per `unicode-range` nur Latein ab, kyrillische Zeichen fallen automatisch eine
Stufe weiter. Ein russischer Titel und der lateinische Vereinsname im selben
Satz bekommen so jeweils die richtige Schrift.

Beim Nachladen wird **nach URL dedupliziert, nicht nach Schriftschnitt** —
Variable Fonts liefern für mehrere Schnitte dieselbe Datei. 14 `@font-face`,
6 Dateien.

### Behobener Fehler: der Umschalter zog Kyrillisch auf jede Seite

Der Sprachumschalter trug „Русский" als Vorlesetext auf *jeder* Seite, auch auf
deutschen. Damit lud jede deutsche Seite die kyrillischen Schriftschnitte mit —
gemessen 137 KB, die die Hauptzielgruppe auf dem Mobilfunknetz bezahlt hätte,
ohne sie je zu sehen.

Die Ansage steht jetzt in der Sprache der jeweiligen Seite („Sprache wechseln
zu Russisch"). Nebeneffekt: Eine deutsche Vorlesestimme kann das überhaupt
aussprechen. Ein Test hält deutsche Seiten frei von kyrillischen Zeichen.

### Behobener Fehler: die Kopfzeile passte nicht mehr

Der Umschalter hat die Kopfzeile bei 360 px auf 451 px aufgezogen und
Notausgang und Menüknopf aus dem Bild geschoben — genau das, was er nicht tun
durfte. Er steht dort jetzt erst ab `sm` und darunter im aufgeklappten Menü,
dasselbe Muster, das der Notausgang selbst schon nutzt.

Beim Nachmessen fielen zwei Dinge auf, die **schon vorher** kaputt waren:

- Bei **320 px** lief die Kopfzeile auf 340 px über — waagerechtes Scrollen,
  WCAG 1.4.10. Die Wortmarke darf dort jetzt schrumpfen.
- Bei **1024 px** passte die Desktop-Navigation nicht (1115 px deutsch). Sie
  brach still um und zog die Kopfzeile auf 133 px; auf Russisch wurde daraus
  ein dreizeiliger Menüpunkt. Sie ist jetzt ab `xl` sichtbar statt ab `lg` —
  zwischen 1024 und 1280 greift das Burger-Menü, das ohnehin vollständig
  bedienbar ist. **Das ist eine sichtbare Designänderung für Deutsch.**

### Leichte Sprache — Fassung, nicht Sprache

Leichte Sprache ist eine **Fassung** einer Seite (`pages.fassung`), keine Sprache
in der `languages`-Tabelle. Die Unterscheidung ist die ganze Entscheidung:

- Sie **ist** Deutsch. Das `lang`-Attribut bleibt `de`, sonst liest eine
  Vorlesehilfe den Text falsch aus. Es gibt kein `hreflang` dafür: `de-x-leicht`
  ist ein Private-Use-Subtag nach BCP 47, den Google als Fehler meldet statt
  versteht.
- Sie bekommt trotzdem eine **eigene Adresse** unter `/leichte-sprache/…`. Als
  blosser Baustein wäre sie nicht verlinkbar, nicht als Lesezeichen speicherbar
  und nicht auffindbar. BITV 2.0 § 4 erwartet einen eigenen, von der Startseite
  aus erreichbaren Bereich — kein aufklappbarer Kasten auf Seite drei.

Umgesetzt über dieselbe Übersetzungsgruppe wie die Sprachfassungen: schwere und
leichte Fassung teilen `uebersetzungs_gruppe` und dürfen denselben Slug tragen
(Unique-Index jetzt auf `(locale, fassung, slug)`). Von der schweren Fassung
führt ein sichtbarer Hinweis ganz oben zur leichten und zurück. Fehlt die leichte
Fassung, ist die Adresse ein ehrliches 404 mit den Auswegen der Fehlerseite —
**kein** Rückfall auf den schweren Text, denn wer Leichte Sprache braucht, dem
hilft der schwere Text nicht.

Die Typografie (grössere Schrift, Zeilenabstand 1,9, Flattersatz, keine
Trennung, keine Kursivschrift) hängt an `[data-fassung='leichte-sprache']` am
`<main>` und gilt so automatisch für alle Bausteine — auch für die, die es heute
noch nicht gibt. Sie steht mit `!important`, weil die Bausteine eigene
Tailwind-Utilities mitbringen (`leading-relaxed`, `text-[1.0625rem]`), deren
Selektoren höhere Spezifität haben. Das ist konsistent mit `a11y.css`: Leichte
Sprache ist eine verbindliche Darstellungsregel, kein Vorschlag.

Der alte Baustein-Typ `leichte_sprache` bleibt bestehen — eine kurze
Zusammenfassung *innerhalb* einer schweren Seite ist etwas anderes als eine
vollständige Fassung, und beides ist üblich.

### Behobener Fehler: gepflegter `meta_title` blieb wirkungslos

Das Feld `meta_title` gab es im Panel seit Anfang an — es landete nur nie im
`<title>`. Das Layout hängte pauschal „ - Kein Einzelfall e.V." an den
Seitentitel, `Page::seiteTitel()` wurde von keiner öffentlichen View benutzt.
Aufgefallen ist das lange nicht, weil **alle 24 Altseiten** zufällig genau dieses
Suffix im `meta_title` tragen — sichtbar wäre der Fehler erst geworden, sobald der
Verein das Feld einmal ändert. Jetzt setzt eine Seite mit gepflegtem `meta_title`
den Titel vollständig (Blade-Section `vollertitel`); ohne bleibt das Muster der
Altseite erhalten, damit sich die Suchergebnisse beim Umzug nicht verändern.

---

## Barrierefreiheit messen

`npm run test:a11y` prüft mit axe-core neun repräsentative Seiten in jeder
freigeschalteten Sprache, dazu die vier Zustände der Darstellungs-Einstellungen
(Panel offen, hoher Kontrast, Dunkelmodus, grösste Schrift) und den Reflow bei
320 px. 36 Durchläufe.

Der erste Lauf fand neun echte Verstösse. Der schwerwiegendste: Im Modus
**„hoher Kontrast"** und im **Dunkelmodus** lag der Fussbereich bei 1,36:1 statt
4,5:1. Ursache war, dass `--color-on-green-soft/-hand/-line` in den beiden Modi
nicht mit umdefiniert wurden — der Fussbereich wurde gelb, seine Schrift blieb
hellgrün. Ausgerechnet die Barrierefreiheits-Einstellung machte damit einen Teil
der Seite unlesbar. Das ist der schlimmste Fall: Wer sie einschaltet, braucht sie.

Weiter gefunden und behoben: geplante Gruppen standen auf `opacity-75` und lagen
damit bei 3,1:1 — betroffen war der Satz, der erklärt, warum man sich nicht
anmelden kann. Die Einstiegskarten der Startseite liefen bei 320 px über. Das
dekorative Wasserzeichen im Hinweisband war ein echter Textknoten mit 1,08:1 und
liegt jetzt als Pseudoelement in der CSS.

**Ein grüner Lauf heisst nicht „barrierefrei".** axe-core findet je nach Quelle
30–50 % der Verstösse. Es sieht nicht, ob ein Alternativtext etwas Sinnvolles
sagt, ob die Reihenfolge logisch ist oder ob die Sprache verständlich bleibt.
Der manuelle Durchgang und ein Test mit einer echten Vorlesehilfe bleiben nötig.

---

## 12. Die Startseite wird pflegbar (30.07.2026)

Der Verein wollte die Überschrift der Startseite ändern und fand sie im Panel
nicht. Zu Recht: Die Startseite war die einzige der 25 Seiten ohne Datensatz —
eine feste Blade-Datei. Zwei Dinge fehlten dafür.

### Sie ist jetzt ein Datensatz wie jede andere

`pages` hat eine Zeile mit dem Slug `startseite`; `PageController::start()` holt
sie wie jede andere Seite, inklusive des sichtbaren Rückfalls auf die
Standardsprache. Angelegt wird sie vom `StartseiteSeeder` — und zwar nur, wenn
es sie noch nicht gibt: Nach dem ersten Lauf gehört sie der Redaktion, und ein
zweiter Lauf darf ihr nicht dazwischenfunken.

Drei Stellen, an denen der Slug **nicht** auftauchen darf:

- `Page::pfad()` liefert für sie `/`, nicht `/startseite`. Sonst stünde sie
  unter der falschen Adresse in Sitemap, hreflang und Sprachumschalter.
- `/startseite` leitet mit 301 auf `/` um. Ohne das gäbe es denselben Inhalt
  unter zwei Adressen — genau der doppelte Inhalt, dessen Vermeidung dem Kunden
  zugesagt ist.
- Aus der Sitemap-Liste der festen Übersichten ist `start` entfernt. Sie kommt
  jetzt aus dem Datensatz; beides zusammen wäre derselbe Eintrag zweimal. Als
  Nebeneffekt nennt hreflang nun die Übersetzungen, die es wirklich gibt, statt
  aller Sprachen, die die Route theoretisch ausliefert.

`home.blade.php` gibt es weiterhin, aber nur noch als Rahmen: Sie rendert die
Bausteine ohne Seitenkopf, ohne Brotkrumen und ohne angehängten Kontaktschluss.
Der Seitenkopf muss weg, weil die Überschrift im Aufmacher steht — sonst hätte
die Startseite zwei `<h1>`.

Fehlt der Datensatz, ist `/` ein 404. Bewusst kein stiller Rückfall auf die
alten fest verdrahteten Texte: Der gäbe der Startseite wieder zwei Quellen, und
niemand sähe, welche gerade gilt.

### Fünf Bausteine haben Eingabefelder bekommen

Ein Datensatz allein hätte nichts genützt — `hero` und die anderen Bausteine der
Startseite hatten ausser `typ` und `titel` kein einziges Feld im Panel
(offener Punkt A3). Nachgeholt für die fünf, aus denen die Startseite besteht:

| Baustein | Neue Felder |
|---|---|
| `hero` | Überzeile, Überschrift, Text, handschriftlicher Zusatz, bis zu zwei Knöpfe |
| `quick_access` | Unterzeile, Karten (Zeichen, Überschrift, Text, Ziel, Linktext) |
| `cta_band` | Überzeile, Leitsatz, Kleingedrucktes, Knöpfe |
| `contact_close` | Text, Bedienhinweis, Knöpfe |
| `hilfe_box` | Überschrift, „nur die zwei wichtigsten Nummern" |
| `text` | zusätzlich Überzeile, handschriftlicher Zusatz, ein Knopf |

### Die restlichen Bausteine, nachgezogen (31.07.2026)

Damit sind alle inhaltstragenden Bausteine im Panel pflegbar — der Rest von A3.

| Baustein | Neue Felder |
|---|---|
| `topic_list` | Unterzeile, Themen (Beschriftung, Ziel, Zeichen), Verweis „alles anzeigen“ |
| `stat_strip` | Kennzahlen (Wert, Bezeichnung) |
| `inhalts_hinweis` | Thema, „schon aufgeklappt zeigen“ |
| `embed` | Anbieter, Adresse, Beschreibung, Direktlink, Datenschutz-Link, Höhe |
| `donation_options` | Bankverbindung, PayPal, betterplace-Projekte, Spendenbescheinigung |
| `team_grid` | Bereichsauswahl (leer = alle) |
| `group_list` | Auswahl Selbsthilfe- / Arbeitsgruppen |

Zwei Bausteine tragen **keinen eigenen Inhalt**: `team_grid` und `group_list`
zeigen, was unter „Vorstand & Team“ und „Gruppen“ gepflegt ist. Im Seiten-Panel
wird nur der Ausschnitt gewählt — die Personen und Gruppen selbst haben ihre
eigene Verwaltung. Ein Feld „Bereich“ ist deshalb eine Auswahl aus den real
vorhandenen Bereichen, kein Freitext: Ein Tippfehler ergäbe sonst eine leere
Fläche.

`contact_form` bleibt bei Titel und (automatischer) Herkunft — es hat keinen
weiteren pflegbaren Inhalt. `leichte_sprache` ist kein Seiten-Baustein mehr,
sondern die Fassungs-Mechanik (siehe Mehrsprachigkeit).

**Der camelCase-Fallstrick:** `topic_list` hat die Prop `alleUrl`. Der
Feldschlüssel muss exakt so heissen — Blade zieht die Prop zwar auch aus
`alle-url`, aber der Datensatz speichert genau den Schlüssel, den das Feld
schreibt. Ein Rendering-Test hält fest, dass `alleUrl` bei der Komponente
ankommt.

### Die handgezeichnete Linie

Das Erkennungszeichen aus dem Mockup: ein Schwung unter einem Teil der
Überschrift. Der Verein markiert diesen Teil mit `*Sternchen*`, wie beim
Fettschreiben in einer Nachricht. Ein zweites Titelfeld wäre die naheliegende
Alternative gewesen; dann müsste die Redaktion aber im Kopf zusammensetzen,
welches Feld vorne steht, und die Linie könnte nie in der Mitte eines Satzes
sitzen.

Gezeichnet wird sie in der CSS (`.swash` in `app.css`), als Hintergrundbild mit
`box-decoration-break: clone`. Der erste Versuch war ein absolut positioniertes
`<svg>` wie im Mockup — das hängt am ersten Zeilenfragment und sass, sobald die
Überschrift umbrach, als kurzer Haken hinter dem letzten Wort. Auf dem Handy ist
der Umbruch der Normalfall. Als Hintergrund bekommt jede Zeile ihre eigene
Linie, ohne eine Zeile JavaScript, und ein Screenreader stolpert nicht darüber.

In `a11y.css` steht der Umriss ein zweites und drittes Mal — in einer `data`-URI
lässt sich keine Custom Property einsetzen, und Vereinsgrün hätte im Modus
„hoher Kontrast" 1,7:1 auf Schwarz. Dunkelmodus bekommt das aufgehellte Grün,
hoher Kontrast Gelb wie jeder andere Akzent dort.

### Was nebenbei aufgefallen ist

**Leere Felder wuchsen bei jedem Speichern mit.** Das Panel schickt jedes
sichtbare Feld mit, auch die unausgefüllten. Ohne Gegenmassnahme sammelte jeder
Baustein Schlüssel mit `null` an, und jedes Speichern sähe beim Vergleich zweier
Stände wie eine inhaltliche Änderung aus. `PageBlock` räumt beim Speichern auf —
an einer Stelle, für alle Schreibwege. `false` und `0` bleiben stehen, das sind
Angaben.

**Halb ausgefüllte Knöpfe und Karten.** Im Panel entsteht so etwas mit einem
Klick: Eintrag hinzufügen, Felder leer lassen. Ergebnis wäre ein `<a href="">` —
für die Tastatur ein Stolperstopp, für einen Screenreader ein Link ohne Namen
und damit ein Verstoss gegen WCAG 2.4.4. Der Helfer `knoepfe()` sortiert sie
aus, die Einstiegskarten filtern sich selbst.

**Tote Sprungmarken.** Das Inhaltsverzeichnis langer Seiten listete jeden
Baustein mit Überschrift, obwohl nur Textbausteine ein Sprungziel setzen.
Solange nur Textbausteine pflegbar waren, fiel das nicht auf. Jetzt filtert
`page.blade.php` darauf — ein Verzeichnis, das ins Leere springt, fällt
ausgerechnet dem auf, der es benutzt, weil er nicht scrollen kann.

## 14. Demo-Übersetzungen für Englisch und Russisch (31.07.2026)

Für Vorführungen sollen Deutsch, Englisch und Russisch auswählbar sein und
Inhalt zeigen. Zwei getrennte Probleme dahinter.

### „Unter Sprachen steht nichts“

Dieselbe Ursache wie bei der Startseite: Der `SprachenSeeder` legt de/en/ru an,
läuft aber nur bei leerer Datenbank (über den `AltseiteSeeder`) und auf dem
Server gar nicht. Eine bestehende Datenbank bekam die Sprachtabelle nie — das
Panel zeigte unter „Sprachen“ eine leere Liste, der Umschalter hatte nichts
anzubieten.

Behoben mit der Migration `2026_07_31_120000_sprachen_sicherstellen`. Sie legt
die drei Sprachzeilen an, idempotent, auch auf dem Server. Bewusst **ohne**
Freischaltung: Englisch und Russisch bleiben `aktiv = false`. Das ist die
Sicherheitslinie — eine Sprache wird erst sichtbar, wenn ihre Inhalte
freigegeben sind.

### Die Inhalte: maschinell, für die Demo, ungeprüft

Der `UebersetzungenSeeder` legt englische und russische Fassungen der Kernseiten
an — **Startseite, Verein, Anfragen, Spenden** — und schaltet en/ru frei. Die
Texte stehen in `database/seeders/data/uebersetzungen.json`.

> ⚠️ **Maschinelle Übersetzung.** Ein Entwurf, damit der Umschalter etwas zeigt.
> Der Verein prüft und korrigiert im Panel — besonders Russisch, das niemand von
> uns gegenlesen kann.

Deshalb hängt der Seeder an **keiner** Migration und läuft **nicht** beim
Deploy. Er wird von Hand nur auf der Demo-Datenbank ausgeführt:

```bash
php artisan db:seed --class=UebersetzungenSeeder
```

So landet kein ungeprüfter Text versehentlich live.

**Warum nur vier Seiten und nicht alle 24:** Eine halb übersetzte Seite sieht
kaputt aus. Eine fehlende dagegen fällt sauber auf den eingebauten, sichtbaren
Rückfall zurück — deutscher Inhalt mit einem Hinweis in der Zielsprache, genau
für diese Zielgruppe so vorgesehen. Lieber wenige Seiten ganz als viele halb.
Weitere Seiten sind später ein Eintrag mehr im Wörterbuch plus ein Slug in
`UebersetzungenSeeder::KERN`.

**Wie übersetzt wird:** Der Seeder klont jede deutsche Seite — gleiche
Bausteine, gleiche Reihenfolge, gleiche Adresse mit Sprachpräfix (`/en/verein`).
Übersetzt werden nur bekannte Textfelder (`titel`, `text`, `absaetze`, `label`
…). Alles andere bleibt: `url`, `icon`, `variant`, IBAN, E-Mail-Adressen. Ein
übersetzter Link wäre ein toter Link, eine „übersetzte“ IBAN schlicht falsch.
Kennt das Wörterbuch einen Satz nicht, bleibt er deutsch stehen — sichtbar
unübersetzt ist ehrlicher als falsch.

**Notrufnummern** werden nicht erfunden. Die deutschen Nummern (110, 116 006 …)
gelten in Deutschland unabhängig von der Sprache; nur ihre Beschriftungen sind
übersetzt.

## 15. Sprachumschalter im jw.org-Stil (31.07.2026)

Die alte Linkliste (》DE EN RU《 als Pillen) sah schlicht aus und **skaliert
nicht**: Bei zwanzig Sprachen sprengt sie die Kopfzeile. Vorbild ist jetzt der
Umschalter von jw.org — ein **Weltkugel-Knopf**, der ein Panel mit allen Sprachen
öffnet, mit **Suchfeld** und scrollbarer Liste.

Die beiden nicht verhandelbaren Zusagen bleiben eingelöst:

- **Ohne JavaScript bedienbar.** Der Aufklapper ist ein natives `<details>` —
  dasselbe Muster wie Mobilmenü und Akkordeon. Auf/Zu, Tastatur und
  Screenreader-Ansage kommen vom Browser. Das **Suchfeld ist reine Verbesserung**:
  Es steht als `hidden` im HTML und wird erst von einem kleinen, per CSP-nonce
  erlaubten Skript eingeblendet. Ohne Skript sieht man die volle Liste, nur
  ungefiltert. Die Suche filtert über Eigenbezeichnung, deutschen Namen und
  Kürzel — 》Русский《, 》Russisch《 und 》ru《 finden alle dieselbe Zeile.
- **Kyrillisch lädt nur, wo es gebraucht wird.** Die Eigenbezeichnungen stehen im
  Panel, also innerhalb des `<details>`. Auf einer deutschen Seite trägt der Knopf
  selbst nur die aktuelle Sprache (》Deutsch《). `test_deutsche_seiten_enthalten_
  keine_kyrillischen_zeichen` bewacht das: kyrillische Zeichen ausserhalb der
  Aufklapper zögen die kyrillischen Schriftschnitte auf jede deutsche Seite.

Zwei Varianten aus einer Datei: Im Kopf das Weltkugel-Dropdown (`<details>`,
Escape/Aussenklick schliesst, Suche fokussiert beim Öffnen). Im Mobilmenü — das
selbst schon ein `<details>` ist — eine flache, beschriftete Liste ohne zweiten
Aufklapper (kein verschachteltes `<details>`, sonst bräche der Kyrillisch-Test).
Beide teilen sich Suche, Liste und Leermeldung; dasselbe Skript verbessert beide.

Der Browser-A11y-Test liest die freigeschalteten Sprachen aus
`header nav[aria-label] a[hreflang]` — die Struktur (Landmarke + hreflang-Links)
bleibt deshalb erhalten, auch wenn die Liste jetzt in einem Aufklapper steckt.

**Vor dem Namen steht ein Sprachkürzel-Badge (》DE《 》EN《 》RU《), keine Flagge.**
Das war eine bewusste Entscheidung, kurz mit Flaggen gebaut und wieder verworfen.
Flaggen stehen für Länder, nicht für Sprachen — und bei dieser Zielgruppe ist das
heikel: Russischsprachige Besucher kommen oft gerade *nicht* aus Russland
(ukrainische Geflüchtete, aus Russland Geflohene); die russische Flagge im Menü
kann abweisend bis verletzend wirken, ausgerechnet für die Menschen, die der
Verein erreichen will. jw.org — das Vorbild — nutzt aus demselben Grund keine
Flaggen. Das Kürzel ist länderneutral, eindeutig und skaliert auf jede Sprache
ohne ein einziges Bild. **Bitte nicht „hilfreich“ auf Flaggen zurückbauen.**

## 16. Notausgang: mobil immer im Kopf (31.07.2026)

Rückmeldung von Kevin: Auf dem Handy kam man an den Notausgang nur über das Menü
— unpraktisch. Ursache war die alte Platzaufteilung: In der Kopfzeile war der
Notausgang `hidden sm:block`, also unterhalb von 640 px ausgeblendet. Auf dem
Handy blieb er nur in der unteren Leiste (dort als kurzes rotes „Exit", leicht zu
übersehen und nicht als *Notausgang* erkennbar) und im aufgeklappten Burger-Menü.

Jetzt steht er auf **jeder** Größe im klebenden Kopf, oben rechts, ohne dass man
etwas aufklappen muss — wie die prominente Platzierung auf der Altseite. Platz
dafür ist da, seit der Barrierefreiheits-Knopf als fixes Tab an den linken Rand
gewandert ist (Abschnitt 15 … eigentlich der a11y-Teil): Die Kopfzeile hat einen
Slot frei bekommen.

Der Exit-Button (`variant="header"`) ist dafür responsiv geworden: unterhalb von
380 px ein 40-px-Kreis nur mit Symbol (voller Tap, kein Umbruch), ab 380 px die
Pille mit Beschriftung „Notausgang". Die Vorlesehilfe bekommt den Namen weiterhin
aus dem sr-only-Text, auch im Symbol-Zustand.

Aus dem Burger-Menü ist der Notausgang entfernt — er wäre dort ein dritter,
versteckter Ort. Geblieben sind zwei bewusste Stellen: der klebende Kopf (immer
im Blick) und die untere Leiste (Daumenreichweite in akuten Situationen). Die
Position beider ist fest — Verlässlichkeit vor Eleganz.

---

## 17. Paket 1 aus dem Besprechungs-Abgleich (05.08.2026)

Grundlage: `docs/Abgleich-Besprechung-2026-08-02.md` — der Abgleich zwischen dem
Strukturpapier des Vereins, dem Protokoll der Besprechung vom 02.08.2026 und dem
Code-Stand. Umgesetzt wurde das darin als „Paket 1" bezeichnete Bündel.

### 17.1 Trigger-Warnung — die eine Entscheidung, die zählt

Erste Zeile im Strukturpapier: ein vorgeschalteter Hinweis, den man wegklicken
kann (kommt beim nächsten Besuch wieder) oder dauerhaft abbestellt.

Der Hinweis steht als **`<dialog open>` im Server-HTML** und wird von JavaScript
per `showModal()` zum echten Dialog hochgestuft. Das ist die ganze Konstruktion,
und die Richtung ist der Punkt:

- **Ohne JavaScript** ist ein `<dialog open>` ein gewöhnlicher Block im
  Seitenfluss — der Hinweis steht sichtbar über allem.
- **Mit JavaScript** kommen Fokusfalle, abgedunkelter Hintergrund und ESC vom
  Browser, nicht von uns.

Andersherum wäre es fahrlässig: Ein Overlay, das erst JavaScript aufbaut, gibt
bei jedem Skriptfehler und in jedem Browser mit abgeschaltetem JavaScript den
Inhalt **ungewarnt** frei. Bei dieser Zielgruppe ist das der eine Fehler, den man
nicht machen darf.

Drei Dinge, die sonst leicht danebengehen:

1. **Kein `<h2>` im Dialog.** Er steht im Quelltext vor dem Seiteninhalt; eine
   Überschrift dort führte die Gliederung an, bevor die `h1` der Seite kommt —
   genau der Fehler, den die A11y-Toolbar an derselben Stelle schon einmal
   gemacht hat. Der Name kommt über `aria-labelledby` von einem `<p>`. Auch
   Überschriften aus den Bausteinen werden beim Rendern im Dialog verworfen,
   damit das nicht über das Panel wieder hereinkommt.
2. **Knopf und Kästchen erscheinen erst, wenn sie verdrahtet sind**
   (`ke-trigger-bereit` an `<html>`), nicht schon dann, wenn JavaScript
   grundsätzlich läuft. Ein abgebrochenes Bundle ist sonst genau der Fall, der
   durchrutscht und tote Bedienelemente hinterlässt.
3. **Versteckt wird vor dem ersten Zeichnen**, über ein Inline-Skript im `<head>`
   — dasselbe Muster wie bei den Darstellungs-Einstellungen. Wer den Hinweis
   abbestellt hat, soll ihn nicht bei jedem Seitenaufruf kurz aufblitzen sehen.

Gespeichert wird an zwei Orten, und das ist der Unterschied, den der Verein
gefordert hat: `sessionStorage` für „weggeklickt" (kommt beim nächsten Besuch
wieder), `localStorage` für „nicht mehr anzeigen". Beides ist eine Einstellung
auf ausdrücklichen Wunsch und damit einwilligungsfrei; an den Server geht nichts.

**Die Bedienelemente wurden am 05.08.2026 überarbeitet** — Rückmeldung von Kevin
zur ersten Fassung: „Die Buttons gehen gar nicht." Sie waren zu Recht bemängelt:
drei Elemente in drei Größen, eines per `ms-auto` an den Rand geschoben.

Jetzt gilt:

- **„Nicht mehr anzeigen" ist ein Kontrollkästchen, kein Knopf.** Der Verein hat
  es selbst so beschrieben: „…oder aber auch *auswählen* kann". Auswählen, nicht
  drücken — es ist eine Einstellung und keine Handlung. Als dritter Knopf stand
  es gleichrangig neben zwei Handlungen und zwang zu einer Entscheidung, die
  niemand treffen wollte. Als Kästchen bleiben unten genau zwei Wege:
  weiterlesen oder gehen.
- **Beide Wege kommen aus `x-ui.button`**, in derselben Größe. Eigene Klassen
  danebenzuschreiben ist genau der Weg, auf dem sie beim ersten Mal
  auseinandergelaufen sind.
- **Jede Knopf-Variante trägt jetzt einen Rahmen**, die gefüllten einen
  durchsichtigen. Ohne das ist ein umrandeter Knopf 2 px höher als ein gefüllter
  daneben, weil der Rahmen zur Höhe dazukommt — das betraf bisher **jedes** Paar
  aus `primary` und `ghost` im ganzen Projekt, nicht nur diesen Dialog.
- Neue Variante **`alert`** (umrandet, Warnfarbe), ausschliesslich für den
  Notausgang. Bewusst **nicht** in `PageForm::KNOPF_AUSSEHEN`: Im Panel soll
  niemand versehentlich einen roten Spendenknopf bauen können.

Gemerkt wird beim `close`-Ereignis und nicht beim Klick. Wer das Kästchen
ankreuzt und dann ESC drückt, hat seine Entscheidung genauso getroffen — am
Klick zu horchen hätte diesen Weg verschluckt. `bedienung.mjs` spielt beide
Wege durch und misst zusätzlich, dass die zwei Knöpfe gleich hoch sind und auf
einer Linie stehen.

**Der Inhalt ist eine ganz normale Seite** (`Page::TRIGGER_SLUG`, `/trigger-warnung`)
— mit Übersetzungen, Leichter Sprache und Pflege im Panel. Ausschalten heisst:
Seite auf Entwurf setzen. Kein Deployment nötig.

> ⚠️ Der Wortlaut ist ein Vorschlag und bewusst nüchtern gehalten — er beschreibt,
> worum es auf dieser Website geht, und trifft keine Aussage im Namen des Vereins.
> Steht als Rückfrage auf der Übergabe-Checkliste.

**Folgen für die Tests:** Ein modaler Dialog hält den Fokus fest und macht den
Rest des Dokuments inert. Beide Browser-Testläufe bestellen den Hinweis deshalb
vorab ab (`localStorage`), sonst prüfte axe auf jeder Seite immer wieder denselben
Dialog und nie die Seite darunter — der Lauf wäre grün und sagte nichts. Der
Dialog selbst bekommt einen eigenen axe-Lauf und einen eigenen Abschnitt in
`bedienung.mjs`, der „wegklicken" und „nie wieder" wirklich durchspielt.

### 17.2 Glossar

Eigene Tabelle (`glossary_terms`), eigene Adresse (`/glossar`), Filament-Resource.
Alphabetisch, mit Buchstabenleiste aus echten Ankern — ohne JavaScript bedienbar,
und ein Filter ist es bewusst nicht: Die vollständige Liste bleibt sichtbar, damit
`Strg+F` alles findet.

**Jeder Eintrag hat ein eigenes Sprungziel** (`/glossar#gdb`). Genau das steht in
einer Antwort des Vereins auf eine Anfrage — ein Glossar, aus dem man keinen
einzelnen Begriff verlinken kann, ist nur die halbe Hilfe.

Einsortiert wird nach der **Abkürzung**, nicht nach dem ausgeschriebenen Begriff:
Wer „SGB XIV" im Bescheid liest, sucht unter S. Umlaute bekommen kein eigenes
Fach (`Ö` → `O`) — ein Fach zwischen U und V lässt Einträge verschwinden, die
dort niemand sucht.

> ⚠️ Vom Startbestand sind nur die Einträge veröffentlicht, deren Text sich auf
> eine Aussage des Vereins selbst stützt (OEG und SGB XIV stehen wörtlich im
> Protokoll; SER und IFG sind Auflösungen von Abkürzungen aus den eigenen
> AG-Namen). GdB, Pflegegrad und Persönliches Budget liegen als **Entwurf** in
> der Datenbank. Was hier steht, liest jemand, der danach über eine Frist
> entscheidet — Rechtsauskünfte schreiben wir nicht. Der Verein hat für solche
> Fälle eigene Anwälte zugesagt.

### 17.3 Dokumentenliste mit Verweisen nach draußen

Umsetzung der Entscheidung aus der Besprechung: Antragsformulare werden nicht mehr
selbst gehostet, sondern bei der Behörde verlinkt — Ämter ändern ihre Vordrucke,
und wer einen veralteten Antrag einreicht, verliert Zeit, die er oft nicht hat.

Ein externer Eintrag bekommt statt „PDF-Datei, 180 KB" die **Herkunft** in den
Linktext („Öffnet Deutsche Rentenversicherung") und kein `download`-Attribut —
der Browser ignoriert das bei fremder Herkunft ohnehin, es verspricht nur etwas.
Unter der Liste steht einmal, warum verlinkt statt abgelegt wird; je Zeile
wiederholt wäre es Rauschen.

### 17.4 Baustein `partner_logos`

Ein Baustein für vier Kategorien des Strukturpapiers (1.9 Kooperationen,
1.10 Schirmherrschaften, 1.11 Botschafter, 7.1 Förderungen). Sie unterscheiden
sich in der Überschrift darüber, nicht in der Darstellung — vier fast gleiche
Bausteine wären vier Stellen, an denen später etwas auseinanderläuft.

**Das Logo ist dekorativ (`alt=""`), der Name steht als Text daneben.** Ein Logo
mit `alt="Aktion Mensch Logo"` liest sich vorgelesen als „Aktion Mensch Logo Link
Aktion Mensch" — die Doppelung stört genau die Menschen, für die der
Alternativtext gedacht ist. Ohne Logo trägt der Name allein: Der Verein soll
Partner eintragen können, bevor er eine Bilddatei hat.

### 17.5 Girocode (QR-Code für die Überweisung)

`App\Support\Girocode` erzeugt einen EPC069-12-Datensatz und rendert ihn als
Inline-SVG. **Lokal, ohne Fremddienst** — es gibt genug Anbieter, die so einen
Code „kostenlos" ausliefern und dabei mitlesen, wer ihn ansieht.

Die Bibliothek (`chillerlan/php-qrcode`) lag ohnehin im Projekt, weil Filament sie
für die Zwei-Faktor-Anmeldung nutzt. Sie steht seitdem **ausdrücklich in der
`composer.json`**, damit sie nicht mit einem Filament-Update verschwindet.

Zwei Festlegungen, die nicht verhandelbar sind:

- **Fehlerkorrektur M.** Schreibt EPC069-12 vor; bei einer anderen Stufe
  verweigern Banking-Apps den Code.
- **Fest schwarz auf weiss**, gegen die sonstige Regel dieses Projekts. Ein
  QR-Code ist kein Text, sondern etwas, das eine Kamera lesen muss — Dunkelmodus
  oder invertierte Farben machen ihn für einen Teil der Scanner unbrauchbar. Die
  IBAN daneben ist der Weg, der ohne Kamera, ohne App und ohne Smartphone
  funktioniert; sie bleibt deshalb gleichberechtigt stehen.

Bei unvollständiger IBAN entsteht **kein** Code. Ein QR-Code auf eine halbe IBAN
führte eine Spende ins Leere.

### 17.6 Nachgetragene Datensätze und neue Bereiche

- Selbsthilfegruppe **„Killing me Softly"** und **AG 07 („Traumabegleiter"-App)**,
  beide mit Status „geplant" — uns wurde kein Termin genannt, und eine Gruppe als
  offen auszuweisen, zu der niemand kommen kann, ist bei dieser Zielgruppe die
  schlechtere Auskunft.
- **Schutzkonzept, Beschwerdemanagement, Projekte, Publikationen** als **Entwurf**.
  Sie sind leer, und das ist der Punkt: Ein Schutzkonzept ist eine
  Selbstverpflichtung — was darin steht, muss der Verein einhalten können. Wir
  legen Adresse und Struktur an, den Text schreibt er. Ins Menü kommen sie erst
  nach dem Freigeben; ein Menüpunkt auf einen Entwurf wäre ein 404.

Die Zwischenüberschriften von „Projekte" und „Publikationen" stammen wörtlich aus
dem Strukturpapier (5.1/5.2 und 12). Wo dort nur der Bereichsname steht, steht
auch hier nur ein leerer Abschnitt.

### 17.7 Zwei Dinge am Rande, die dabei aufgefallen sind

**`bin/start` hätte auf einem frisch eingerichteten Rechner nur zwei Seiten
angelegt.** Die Prüfung lautete `Page::count() == 0` — aber die Migrationen legen
Startseite und (jetzt) Trigger-Warnung *vor* dieser Prüfung an. Damit war die
Zahl nie 0, und der `AltseiteSeeder` mit den 23 Inhaltsseiten lief nie. Gezählt
werden jetzt nur die Inhaltsseiten.

**YouTube fehlt weiterhin im Fuß.** Der Kanal wurde in der Besprechung genannt,
seine Adresse aber nicht. Eine geratene Adresse führt entweder ins Leere oder,
schlimmer, zu einem fremden Kanal. Die Zeile steht auskommentiert in
`config/navigation.php`; es fehlt nur die Adresse. Discord fehlt bewusst — der
Verein hat die Plattform in derselben Besprechung selbst infrage gestellt.

## 18. Gespeicherte Einstellungen — der Weg zurück (05.08.2026)

Anlass: Kevins Einwand, dass jede Einstellung, die im Browser landet, auch
wieder rückgängig zu machen sein muss. Sein Vorschlag war ein Fusszeilen-Link,
der die Trigger-Warnung erneut öffnet.

**Die Idee stimmt, der Weg hatte zwei Haken.** Man müsste ausgerechnet das
Fenster aufrufen, das man abbestellt hat, um es wieder zu bestellen. Und sie
trägt genau einen Fall: Beim zweiten gespeicherten Wert bräuchte es einen
zweiten Link, beim dritten einen dritten. Der Verein sagt selbst, dass später
mehr dazukommt.

### Was gebaut ist

**`config/speicher.php` ist die eine Liste.** Dort steht jeder Schlüssel, den
diese Website im Browser ablegt. Drei Stellen lesen daraus, keine schreibt eine
eigene Liste:

- der Baustein `speicher_uebersicht` am Ende von `/barrierefreiheit`
- der Knopf „Alles zurücksetzen“ in der Darstellungs-Toolbar
- die Datenschutzerklärung — sie **muss** diese Liste nennen

Der Fuss bekommt einen Link „Gespeicherte Einstellungen“, der auf den Anker
`#gespeicherte-einstellungen` springt. Kevins Fusszeilen-Link also, nur mit
einem Ziel, das mitwächst.

Die Übersicht zeigt je Eintrag den tatsächlichen Zustand („Auf diesem Gerät
gespeichert“ / „Nichts gespeichert“), einen Knopf zum Zurücksetzen und darunter
einen für alles zusammen. Der Zustand steht in einem `aria-live="polite"` —
sonst passiert beim Drücken für eine Vorlesehilfe sichtbar nichts. Ist nichts
gespeichert, wird der Knopf abgeschaltet statt entfernt: Verschwände er, wanderte
bei jedem Klick die halbe Zeile und die Tastaturreihenfolge mit ihr.

### Der Fehler, den das nebenbei behebt

**„Alles zurücksetzen“ in der Darstellungs-Toolbar räumte nicht alles ab.**

Solange es nur die Darstellungs-Einstellungen gab, stimmte die Beschriftung von
selbst. Mit der Trigger-Warnung kam ein zweiter gespeicherter Wert dazu, und sie
wurde stillschweigend falsch — der Knopf löschte weiterhin nur seinen eigenen
Schlüssel. Genau die Sorte Halbwahrheit, die man einer Zielgruppe nicht zumuten
sollte, die auf verlässliche Auskünfte angewiesen ist.

Der Knopf bedient sich jetzt aus derselben Liste. Damit das von jeder Seite aus
geht, liegt sie als `window.keSpeicher` im Kopf jeder Seite — dasselbe Muster
wie `window.keDarstellung` daneben.

### Für später

> ⚠️ **Wer künftig etwas im Browser speichert, trägt es in `config/speicher.php`
> ein.** Sonst lässt es sich nicht zurücksetzen, und die Datenschutzerklärung
> wird unvollständig. Zwei Tests in `SpeicherTest` halten das in beide
> Richtungen fest: Ein wörtlich im Code stehender Schlüssel muss in der Liste
> sein, und ein Eintrag der Liste, den niemand mehr schreibt, fliegt auf.

Rechtlich ist alles davon eine Einstellung auf ausdrücklichen Wunsch und damit
einwilligungsfrei (§ 25 Abs. 2 Nr. 2 TDDDG): kein Tracking, keine Kennung,
nichts geht an den Server. Erwähnt werden muss es trotzdem — die
Datenschutzerklärung wird ohnehin neu geschrieben, und diese Liste ist die
Vorlage für den entsprechenden Abschnitt.

---

## 19. Spendenmöglichkeit auf der Startseite (19.09.2026, KEV-10)

Auf der Startseite führten drei Wege zu `/spenden` — Menü, Einstiegskarte,
Hinweisband —, aber nirgends stand die Möglichkeit selbst. Wer spenden wollte,
musste erst die Unterseite finden. In der Besprechung vom 02.08.2026 war
beschlossen worden, die Spendenoption direkt auf der Startseite zu verankern,
samt QR-Code für die Überweisung; im Abgleich stand der Punkt seither auf 🟡.

### Was sich geändert hat

**`donation_options` kann jetzt kompakt.** Neue Felder `eyebrow`, `text`,
`mehr` (Verweis auf die vollständige Seite) und der Schalter `kompakt`. Kompakt
heisst: Einleitung links, Konto und PayPal rechts daneben, volle Seitenbreite
wie die übrigen Bausteine der Startseite. Ohne den Schalter bleibt es die
einspaltige Fassung für die Spendenseite. Der Abschnitt trägt `id="spenden"`,
damit Kopf, Band oder geteilte Links dorthin springen können.

**Die Beschriftungen des Bausteins kommen aus `rahmen.spenden`** (de/en/ru):
„Überweisung", „Bei PayPal spenden", der Hinweis unter dem QR-Code. Vorher
standen sie fest deutsch im Blade — auf der englischen und russischen
Startseite wäre das aufgefallen. Die Angaben des Vereins (IBAN, Empfänger,
Einleitung) bleiben Inhalt und damit im Datensatz.

**Auf der Startseite steht der Baustein vor dem Hinweisband:** Aufmacher ·
Hilfe-Nummern · Unsere Aufgabe · Vereinsarbeit · Mitglieder · **Spenden** ·
Hinweisband · Kontaktabschluss. Das Band fasst danach beide Wege der
Unterstützung zusammen und führt zur vollständigen Spendenseite (betterplace,
Spendenbescheinigung). Inhalt: Konto und PayPal wie auf der Spendenseite, als
Einleitung der Text der Einstiegskarte „Spenden" — alles Wortlaut des Vereins.

**Bestehende Datenbanken** bekommen den Baustein per Migration
(`2026_09_19_120000_spenden_auf_der_startseite_nachtragen`), auf allen
Sprachfassungen der Startseite. `StartseiteSeeder::spendenAnhaengen()` fügt
ihn vor dem Hinweisband ein (fehlt es: vor dem Kontaktabschluss; fehlt auch
der: ans Ende) und rückt die Bausteine dahinter eine Position weiter. Zweimal
laufen ist unschädlich; gepflegte Texte bleiben unangetastet.

**PayPal-Link:** `https://www.paypal.com/donate?business=paypal@kein-einzelfall.de&currency_code=EUR`
— der Spendenlink der Altseite. Ein reiner Link, kein Skript; `BarrierefreiheitTest`
kennt `www.paypal.com` deshalb als erlaubtes Linkziel. Fehlt die Adresse im
Panel, zeigt der Baustein nur den Empfänger und keinen Knopf ins Leere.

### Offen

- **Spenden in der mobilen Leiste** — die Frage aus dem Abgleich (welcher der
  drei Einträge weicht?) ist weiter beim Verein. Bis dahin: Menü und der
  Abschnitt auf der Startseite.
- ~~`/spenden` selbst nutzt für Konto und PayPal noch den Textbaustein~~ —
  erledigt mit KEV-5 (Abschnitt 22).
- **Kontoinhaber für den QR-Code** — siehe Übergabe-Checkliste.

---

## 20. Spendenhinweis für wiederkehrende Besucherinnen (19.09.2026, KEV-6)

Wunsch aus der Besprechung vom 02.08.2026: keine „Battle-Buttons", kein Aufruf
beim ersten Besuch — sondern ein Hinweis, der erst kommt, wenn jemand die Seite
schon ein paarmal genutzt hat, und der nach dem Wegklicken längere Zeit Ruhe
gibt. Abgleich-Punkt „Spenden-Popup ab 5–10 Seitenaufrufen".

### Was es ist — und was nicht

Ein kleiner Kasten unten rechts (`<aside>`, benannter Landmark), der weder den
Fokus an sich zieht noch die Seite verdeckt oder sperrt. Kein `<dialog>`, keine
Fokusfalle, kein Abdunkeln. Wer liest, liest weiter. Ein modaler Dialog wäre bei
dieser Zielgruppe das Gegenteil von unaufdringlich — und läge im schlimmsten
Fall über jemandem, der gerade eine Anfrage schreibt.

Deshalb auch **kein ESC zum Schliessen**: Dreimal ESC ist der Notausgang, und
ein Hinweis, der auf ESC reagiert, stünde dem im Weg. Geschlossen wird über
„Jetzt nicht" oder das X; beides gibt dieselbe Ruhe. Der Knopf „Zum Spenden"
ebenfalls — wer auf der Spendenseite war, braucht keinen Hinweis mehr.

### Wo er steht und wo nicht

Die Entscheidung fällt auf dem Server (`App\Support\SpendenHinweis`): Auf einer
ausgenommenen Seite steht der Kasten gar nicht erst im HTML. Erlaubt sind die
öffentlichen Inhaltsrouten (Start, Seiten, Leichte Sprache, Glossar, Blog,
Veranstaltungen) — eine Liste dessen, was erlaubt ist, damit eine neue Route
erst einmal ohne Hinweis ist. Ausgenommen per Slug: `spenden` (überflüssig),
`anfragen` und `kontakt` (wer eine Anfrage schreibt, wird nicht um Geld gebeten
— das ist der Unterschied zwischen Opferhilfe und Vertrieb), `trigger-warnung`.
Fehlerseiten setzen den Abschnitt `ohne-spendenhinweis`.

Ist die Trigger-Warnung offen, wartet der Kasten auf deren `close`.

### Zählen und Ruhe

`resources/js/spendenhinweis.js`, zwei Schlüssel in `localStorage`
(`ke.spenden.aufrufe`, `ke.spenden.ruhe`), beide in `config/speicher.php` und
damit in der Übersicht auf /barrierefreiheit und per „Alles zurücksetzen"
abräumbar. Schwellen aus `config/spendenhinweis.php`, als data-Attribute am
Kasten — kein Asset-Build bei Änderung. Läuft die Ruhe ab, beginnt der Zähler
neu; der Hinweis kommt also nicht am Tag 31 sofort wieder.

Ohne JavaScript gibt es den Kasten nicht (`hidden` im Server-HTML). Richtig so:
Ohne Skript gäbe es keinen Zähler, also kein „wiederkehrend". Im privaten
Modus dasselbe — lieber nie fragen als jedes Mal.

**Rechtlich** der Eintrag in `config/speicher.php`, der am genauesten hinzusehen
verlangt: Die Ruhezeit ist ein ausdrücklicher Wunsch der lesenden Person, der
Zähler davor nicht. Er speichert eine Zahl ohne Kennung, nichts verlässt den
Browser, und seine einzige Wirkung ist, dass der Hinweis *seltener* erscheint.
Das ist Frequenzbegrenzung im Interesse der lesenden Person — genannt werden
muss er in der Datenschutzerklärung trotzdem (Übergabe-Checkliste).

### Nebenbei behoben

Die Knöpfe der Übersicht „Gespeicherte Einstellungen" hingen an
`data-trigger-braucht-js`. Die Klasse `ke-trigger-bereit` setzt das
Trigger-Skript aber nur, wenn der Dialog auch gezeigt wird — wer ihn abbestellt
hatte, sah auf /barrierefreiheit keine Knöpfe. Ausgerechnet die Person, die sie
braucht. Die Übersicht hat jetzt ihr eigenes Merkmal (`ke-speicher-bereit`).
Aufgefallen im Browser-Test, nicht in PHPUnit: Das HTML war korrekt, nur die
CSS-Regel griff.

### Tests

`SpendenHinweisTest` (Server: wo, wie, mit welchen Schwellen),
`SpeicherTest` kennt das neue Skript, `bedienung.mjs` (Zählen, Wegklicken,
Ruhe, Trigger-Warnung), `barrierefreiheit.mjs` (axe mit sichtbarem Kasten,
Desktop und mobil).

---

## 21. Russisch entfernt (19.09.2026)

Russisch war seit dem 31.07.2026 die dritte Sprache: Sprachzeile inaktiv,
Bedientexte in `lang/ru/`, vier Kernseiten als maschinelle Übersetzung auf
der Demo, kyrillische Schriftschnitte (Literata als Ersatz für Fraunces).
Kevin hat sie gestrichen — niemand im Team konnte sie gegenlesen, und der
Verein hatte nie eine russische Seite angefasst. Bedingung war: Solange
niemand daran gearbeitet hat, kann es komplett raus; sonst nur abschalten.

**Genau so entscheidet die Migration `russisch_entfernen` selbst:** Liegt
`updated_at` einer russischen Seite mehr als eine Minute nach `created_at`,
wird die Sprache nur deaktiviert und eine Warnung geloggt; sonst gehen Seiten
(samt Bausteinen), russische Glossareinträge und die Sprachzeile. Auf einer
Datenbank ohne Russisch tut sie nichts. Drei Tests in `MehrsprachigkeitTest`.

**Aus dem Code raus:** `lang/ru/`, der ru-Eintrag im `SprachenSeeder` (die
alte Migration `sprachen_sicherstellen` ruft den Seeder und legt damit auf
frischen Datenbanken nur noch de/en an), die ru-Werte in
`uebersetzungen.json` (72 Einträge; die Datei ist dabei neu formatiert
worden), `UebersetzungenSeeder` klont nur noch Englisch, die sechs
`*-cyrillic*.woff2` samt ihren 14 `@font-face`-Regeln, Literata im
`--font-display`-Stapel.

**Was bleibt:** das Grundgerüst der Mehrsprachigkeit (Sprachen im Panel
anlegbar, Präfix-Routing, hreflang, Rückfall). Der Test, dass deutsche Seiten
keine fremden Schriftzeichen ausserhalb des Mobilmenüs enthalten, legt sich
jetzt selbst eine Sprache an — die Regel gilt für jede, die einmal kommt. Ein
neuer Test hält fest, dass keine Schriftdatei ohne `@font-face`-Regel im
Repository liegt.

**Achtung, Widerspruch zum Protokoll vom 02.08.2026:** Dort stand „Russisch
vorbereiten, nicht freischalten". Die Streichung ist Kevins Entscheidung vom
19.09.; falls der Verein Russisch weiterhin erwartet, ist das eine Rückfrage
(Übergabe-Checkliste A6).

---

## 22. Spendenseite: PayPal und betterplace wie auf der Altseite (19.09.2026, KEV-5)

Die Altseite hatte auf /spenden/ PayPal (Donate-Link) und zwei
betterplace-Projekte (iframes, ungefragt geladen). Der Import vom Juli hatte
Konto und PayPal als Fliesstext übernommen („IBAN: DE79 …“) und die iframes gar
nicht — das war Absicht (Projektplan, Punkt 5: Drittanbieter-Embed ohne
Consent), aber damit fehlte betterplace auf der neuen Seite komplett.

### Was jetzt steht

Der `donation_options`-Baustein an der Stelle des Textblocks: Konto mit
QR-Code, PayPal als Link, die beiden betterplace-Projekte als Zwei-Klick-
Einbettung mit Direktlink, Spendenbescheinigung. Vor dem Klick steht kein
`<iframe>` und keine betterplace-Adresse ausserhalb eines `<template>` im
Dokument — gemessen: 0 Requests an betterplace vor der Zustimmung, das
Widget danach.

**Eine Quelle für die Angaben: `App\Support\Spenden`.** Konto, PayPal,
Projekte und Bescheinigung stehen dort einmal; Startseite (KEV-10) und
Spendenseite bedienen sich beide. Ein Test hält fest, dass die IBAN auf beiden
Seiten dieselbe ist. „WirWunder" nennt die Altseite nur im Titel — kein Widget,
kein Link, nichts zu übernehmen.

**Umstellung: `Spenden::spendenseiteUmstellen()`**, vom `AltseiteSeeder` auf
frischer Datenbank und von der Migration
`spendenseite_auf_den_baustein_umstellen` auf bestehenden. Gefunden wird der
Kontoblock über die IBAN im Text, nicht über die Überschrift — die englische
Fassung heisst „Donate now". Der Text der Spendenbescheinigung kommt aus dem
vorhandenen Block der Seite, in dessen Sprache. Fehlt der Kontoblock, hat
jemand die Seite umgebaut, und sie bleibt unangetastet.

**Die Zwei-Klick-Texte** („Dieser Inhalt kommt von …", „Inhalt einmalig
anzeigen") kommen jetzt aus `rahmen.embed` statt fest deutsch aus dem Blade —
die Spendenseite gibt es auch auf Englisch.

### Nebenbei gefunden: `->each()` bricht bei `false` ab

Beide Nachtrag-Methoden (`spendenAnhaengen`, `spendenseiteUmstellen`) geben
`false` zurück, wenn nichts zu tun war. `Collection::each()` wertet das als
„aufhören" — hatte die deutsche Seite den Baustein schon, bekam ihn die
englische nie. Betraf auch die KEV-10-Migration, dort nur deshalb nicht
sichtbar, weil beim ersten Lauf noch keine Seite fertig war. Beide Migrationen
nutzen jetzt `foreach`; zwei Tests stellen den Fall nach.

### Tests

`SpendenseiteTest` (Inhalt, Ersetzung statt Ergänzung, gleiche Quelle wie die
Startseite, Migration, Idempotenz, bearbeitete Seite, englische Fassung),
`DatenschutzTest` prüft jetzt die echte Seite statt eines Testblocks,
`barrierefreiheit.mjs` mit /spenden (de/en) und geöffneter Einbettung.


## 23. Flächenwechsel der Abschnitte (20.09.2026, KEV-20)

Aufeinanderfolgende Abschnitte sollen sich abheben — helle Seitenfläche
(`cream`) und Karte (`card`) im Wechsel. Das galt bislang nur auf dem Papier:
Die Seite hat nach Positionsnummer gewechselt (`$loop->index % 2`), aber nur
der Textbaustein hat die Vorgabe umgesetzt. Alles andere stand fest auf
`cream`, und Seitenkopf, „Weiterlesen" und Kontakt-Abschluss wurden gar nicht
mitgezählt. Ergebnis: auf `/selbsthilfegruppen` drei helle Abschnitte
hintereinander, auf `/verein` eine Text-Karte direkt über der Karte
„Weiterlesen", auf der Startseite fünf helle Abschnitte am Stück.

### Eine Regel statt einer Zählung

**`PageBlock::FLAECHEN`** sagt für jeden Bausteintyp, wie er sich verhält:

| Verhalten | Bausteine |
|---|---|
| `wechselnd` — Gegenfläche des Abschnitts davor | `text`, `text_media`, `schritte`, `accordion`, `team_grid`, `group_list`, `quick_access`, `download_list`, `cta_band`, `contact_close`, `contact_form`, `donation_options`, `hilfe_box`, `partner_logos`, `speicher_uebersicht` |
| `anschliessend` — bleibt auf der Fläche davor | `hinweis` (ein Kasten, der zum Text darüber gehört; auf der Karte deckt er deren untere Linie ab, damit keine Naht entsteht) |
| fest | `hero` (`cream`, eigener Verlauf), `topic_list` (`card`, eigene Linien), `stat_strip`, `embed`, `inhalts_hinweis` (`cream`) |

**`PageBlock::flaechenFuer($bloecke, davor: …)`** läuft einmal über die
Bausteinfolge und bestimmt jede Fläche relativ zum tatsächlichen Vorgänger.
`davor` ist, was über dem ersten Baustein steht — auf Inhaltsseiten der
Seitenkopf (`card`), auf der Startseite die Kopfzeile (`cream`). Eine im
Baustein hinterlegte Fläche (`data['auf']`) hat weiterhin Vorrang.
`page.blade.php` rechnet daraus auch die Fläche von „Weiterlesen" und dem
Kontakt-Abschluss: beide nehmen die Gegenfläche dessen, was zuletzt stand.

Die neu wechselnden Bausteine bekamen dafür die Prop `auf`. Kästen darin
(Dokumentenliste, Spendenkästen, Einstiegskarten, Formularfelder, …) stehen auf
der jeweils *anderen* Fläche — sonst verschwämmen sie auf der Karte mit dem
Hintergrund. `block.blade.php` reicht `auf` nur an Bausteine weiter, die die
Prop kennen (`nimmtFlaeche()`); bei allen anderen landete sie sonst als
Attribut `auf="…"` im HTML.

Der Aufmacher hat jetzt unten denselben Abstand wie jeder Abschnitt: Der
Baustein danach steht auf der Karte, und ohne Luft klebte die Karte an den
Knöpfen.

### Terminliste und Glossar

Auf `/veranstaltungen` und `/glossar` folgt auf die Einleitungsbausteine die
eigentliche Liste. Sie nimmt die Gegenfläche des letzten Bausteins, ihre
Karten stehen dann auf der jeweils anderen — sonst ging der Wechsel nur bei
gerader Zahl Einleitungsbausteine auf. Genau das trat ein, als der korrigierte
Abzug der Altseite (Abschnitt 24) der Veranstaltungsseite einen siebten
Textbaustein brachte.

### Nicht gelöst, bewusst

`embed` und `inhalts_hinweis` haben als Seitenbausteine keinen eigenen Rahmen
(`px-4`, `max-w-6xl`) und liefen über die volle Breite. Beide sind auf keiner
Seite im Einsatz; sie bleiben, wie sie sind, bis jemand sie braucht.

### Tests

`SeitengestaltungTest::test_benachbarte_abschnitte_stehen_nie_auf_derselben_flaeche`
geht über alle veröffentlichten deutschen Seiten plus `/veranstaltungen` und
prüft die obersten Kinder von `<main>` — Seitenkopf bis Kontakt-Abschluss —
paarweise. Auf dem alten Stand meldet er als Erstes die Startseite
(`cream > cream > cream > card > cream > cream > cream > cream`).

## 24. Bilder der Altseite, und was der Importer dabei verschluckt hatte (20.09.2026)

Aufgefallen war: Auf der Teamseite fehlten die Fotos. Beim Nachsehen stellte
sich heraus, dass nicht nur die Fotos fehlten.

### Was die Altseite an Bildern hat

Die komplette WordPress-Mediathek (`/wp-json/wp/v2/media?media_type=image`)
umfasst **15 Bilder**: sieben Porträts der Teamseite, den QR-Code der
Spendenseite (den erzeugen wir selbst, siehe 17.5), fünf Logo-Varianten
(unser `public/img/logo.png` deckt das ab) und eine Datei
`w2sx8a07e0l.php_.jpg` ohne Bildmaße — steht als Warnung auf der
Übergabe-Checkliste. Auf den Seiten selbst stehen nur die Porträts und der
QR-Code; alle anderen Seiten sind reine Textseiten. **Es gibt also nichts
weiter zu übernehmen** — die Platzhalterflächen von `text_media` und dem
Aufmacher bleiben, bis der Verein Bildmaterial liefert.

### Der Importer-Fehler

`AltseiteHolen::bloecke()` las Überschriften und Absätze mit dem Muster
`<(h[1-6]|p)[^>]*>` — ohne Wortgrenze hinter dem Tagnamen. Damit passte es
auch auf `<path …>` der SVG-Icons und las von dort bis zum nächsten `</p>`.
Auf der Teamseite steckt so ein Icon im Kontaktknopf jeder Person: Von
Tatjana Belmars Knopf bis zum ersten `</p>` in Franziska Künstlers
Vorstellung — Rolle, Name, Foto, Kurzangaben von Franziska, alles im Bauch
eines vermeintlichen Absatzes. **Vier von sieben Personen fehlten**, und ihre
Texte standen im Profil der jeweils vorigen: Tatjana „sprach" über ihr
Abimotto und ihre Arbeit als Kassenwartin, Nicole Khalil über 17 Jahre
Strafverteidigung.

Zweiter Fehler im selben Muster: `<li>` wurde nie gelesen. Aufzählungen
fehlen deshalb auf neun Seiten (Notfallnummern, Referent/Datum/Ort der
Veranstaltungen, „Warum spenden", Betroffenenrechte). Steht als eigener
Punkt auf der Übergabe-Checkliste — die Seiten sind inzwischen bearbeitet,
ein Neu-Import überschriebe das.

Behoben: `\b` hinter dem Tagnamen, `<li>` als Absatz, und der Inhalt eines
Absatzes darf kein weiteres `<p>`/`<li>`/`<h…>` enthalten. Bilder werden als
`bild` (Quelle, Beschreibung) an den Block gehängt, in dem sie stehen. Für
Elementor-Vorschaubilder (`elementor/thumbs/NAME-<hash>.jpg`, ohne Verweis
aufs Original) wird das Original einmalig über die Mediathek per Dateiname
aufgelöst.

**Nicht gebaut:** zerrissene Absätze zusammenfügen. Andrés erster Satz steht
auf der Altseite in zwei `<p>`; eine Regel „endet ohne Satzzeichen → mit dem
nächsten verbinden" klebte auf zwölf Seiten Adressen, Linklisten und
Zwischenzeilen zusammen. André ist ein benannter Sonderfall im Seeder.

### Bilder holen

```bash
php artisan bilder:holen            # fehlende holen
php artisan bilder:holen --pruefen  # nur Bericht
```

Ablage unter `public/img/altseite/DATEINAME.jpg` (versioniert). Fotos werden
auf 800 px längste Kante verkleinert und als JPEG gespeichert — gezeigt
werden sie 80 px gross; ein 2.000-Pixel-Original wäre ein Megabyte, das
nichts zeigt. Kleine Grafiken bleiben unverändert (ein neu kodierter QR-Code
wird unscharf). EXIF-Drehung wird beachtet. `App\Support\Bild::lokal()`
sagt, ob und wo ein Bild liegt; der Seeder setzt nur Pfade zu Dateien, die
es gibt.

Braucht die PHP-Erweiterung `gd`. Auf dem Entwicklungsrechner (Nobara) gibt
es `php-gd` nur noch für PHP 8.5 — die Dateien im Repo sind deshalb einmalig
mit Pillow nach denselben Regeln erzeugt worden. Der Bericht funktioniert
ohne `gd`.

### Teamseite

`TeamUndGruppenSeeder::teamAusAbzug()` liest Personen samt Foto und löst die
Überleitungen der Seite („Darüber hinaus gibt es viele Menschen …",
„Zusätzlich arbeiten im Hintergrund …") aus den Profilen heraus. Bereiche
folgen der Gliederung der Altseite: **Vorstand** (Vorstandsamt in der Rolle),
**Team** (Landesstelle, Beauftragte, Ehrenamtliche), **Im Hintergrund** (das
stellvertretende Porträt). `teamseiteAufbauen()` setzt die Seite so
zusammen: Einleitung · Vorstand · Überleitung · Team · Hinweis · Porträt.
`team_grid` bekommt dafür je Bereich eine eigene Landmarken-Kennung.

Das Kurzprofil ist der erste Absatz, der mit Satzzeichen endet — Franziska
Künstlers Vorstellung beginnt mit drei Stichpunkten.

Migration `team_vervollstaendigen` zieht bestehende Datenbanken nach. Sie
schreibt bewusst über den Bestand (die Profile waren falsch, nicht bloss
unvollständig) und baut die Seite nur um, wenn sie noch im Seeder-Zustand
ist (ein `team_grid` ohne Einstellungen).

### Nebenbei

Die Altseite hat seit dem Abzug vom Juli auf `/veranstaltungen` einen
Aufzeichnungshinweis, zwei neue Dokumente (Teilnahmevereinbarung,
Gruppenregeln) und einen geänderten Vortragstitel („Von der Krise zur
Stärke"). Die Teilnahmevereinbarung ist im Dokumentenmanifest nachgetragen
und geholt; der Text ist im Abzug, in der Datenbank noch nicht — gehört zum
Checklistenpunkt oben.

### Tests

`TeamUndGruppenTest`: sieben Personen, Biografien nicht vermischt,
Kurzprofil beginnt mit ganzem Satz, Gliederung der Seite, Porträts vorhanden
und eingebunden, Initialen ohne Foto. `ModuleTest` kennt das 122. Dokument.
