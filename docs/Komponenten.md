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
| `stat_strip` | 🔨 | Startseite — gebaut, aber ohne Felder im Panel |
| `quick_access` | ✅ | Startseite — im Panel pflegbar |
| `text_media` | ✅ | „Wer wir sind" |
| `topic_list` | 🔨 | Wissen — gebaut, aber ohne Felder im Panel |
| `event_teaser` | 🔨 | Startseite |
| `news_teaser` | 🔨 | Startseite |
| `cta_band` | ✅ | Startseite — im Panel pflegbar |
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

Offen bleiben `topic_list`, `team_grid`, `group_list`, `embed`,
`donation_options`, `inhalts_hinweis`, `leichte_sprache`, `stat_strip` und
`contact_form` — sie kommen auf der Startseite nicht vor.

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
