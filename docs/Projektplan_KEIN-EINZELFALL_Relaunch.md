# Projektplan: Relaunch KE!N EINZELFALL e.V.

**Angebot:** AN-268
**Kunde:** KE!N EINZELFALL e.V. (Opferhilfe-Verein, Hamburg, gegründet 2024)
**Ansprechpartnerin:** Tatjana Belmar
**Umsetzung:** Nils-Digital (https://nils-digital.de) — **Referenzprojekt**, muss im Footer erwähnt werden
**Aktuelle Seite:** https://kein-einzelfall.de/ (WordPress + Elementor)
**Ziel:** Individuelles Redesign + Umstellung auf Laravel, inkl. Datenbank-Anbindung
**Budget (§19 UStG, netto = brutto):** 9.726,55 EUR (Summe Positionen 11.443,00 € − 15 % Rabatt: 5 % Neukunde + 10 % Kein-Einzelfall-Rabatt). Anzahlung 50 % = 4.863,28 €.
**Zahlungsbedingung:** 50 % Anzahlung bei Auftragsbestätigung, Rest nach Abschluss. Es gelten die AGB der Nils-Digital.
**Deadline:** Keine feste Deadline. Auftraggeberin ist bewusst, dass ihre zahlreichen Wünsche nicht kurzfristig umsetzbar sind — Projekt läuft phasenweise, mit vielen erwarteten Folgeaufträgen. **Start: voraussichtlich September 2026**, sobald die Fördergelder des Vereins freigeschaltet sind. Die Wartezeit bis dahin wird für Fundament-Entwicklung und die Klärung der offenen Punkte (Abschnitt 10) genutzt.
**Langfristige Ausrichtung:** Auftraggeberin möchte sich selbst dauerhaft nicht mit Website-Pflege oder Social Media befassen — spricht für ein Full-Service-Modell (Pflege, Content-Einpflege, ggf. Social-Media-Betreuung durch Nils-Digital) statt reiner Übergabe an ein Kunden-CMS. Relevant für spätere Folgeaufträge/Wartungsvertrag.

---

## 1. Projektkontext

Der Verein bietet eine Austausch- und Informationsplattform für Opfer/Mit-Opfer von Straftaten, Angehörige, Interessierte und Fachpersonen. Themenfelder u.a. Soziales Entschädigungsrecht (OEG/SGB XIV), Schwerbehindertenausweis, Pflegegrad. Der Verein organisiert Selbsthilfegruppen und Arbeitsgruppen, nimmt Mitgliedschaften und Spenden entgegen.

**Wichtig für die gesamte Umsetzung:**
- Die Zielgruppe befindet sich teils in belastenden/schutzbedürftigen Situationen. Klarheit, Ruhe und schnelle Erreichbarkeit der Kernfunktionen (Kontakt, Notausgang) sind kein Nice-to-have, sondern zentral.
- Es werden **besonders schützenswerte personenbezogene Daten nach Art. 9 DSGVO** verarbeitet (Gesundheitsdaten, Opferstatus). DSGVO-Sorgfalt hat oberste Priorität, auch über das gesetzliche Minimum hinaus.
- Dieses Projekt ist ein Referenzprojekt für Nils-Digital — Qualität und Sorgfalt haben Vorrang vor Tempo.
- Barrierefreiheit auf Maximum: WCAG 2.1 AA als vertraglicher Maßstab, AAA wo sinnvoll erreichbar.

> **Ressourcen-Hinweis:** Der Verein stellt bei Bedarf Zugang zu eigenen Anwälten/juristischem Fachwissen sowie Arbeitsmaterialien zur Verfügung. Bei Unsicherheiten — insbesondere zu Datenschutzerklärung, Verarbeitungszwecken sensibler Daten oder Vertragsfragen (AVV) — ist dieser Rückkanal aktiv zu nutzen, statt intern zu raten.

---

## 2. Ausgangslage — technische Bestandsaufnahme

> Verifiziert am 26.07.2026 durch direkte Analyse der Live-Seite. Diese Befunde sind die Grundlage für Migration und Redirect-Mapping.

### 2.1 System

| | |
|---|---|
| CMS | WordPress 7.0.2 + Elementor 4.2.0 |
| Theme | `uterpy` (kommerzielles Charity-Theme) + `uterpy-addon`, kein Child-Theme |
| Hoster | **ALL-INKL.COM** (Neue Medien Münnich), `dd56130.kasserver.com`, IP 85.13.168.30, Apache, HTTP/2 |
| TLS | Let's Encrypt, gültig, Auto-Renewal — in Ordnung |
| SEO-Plugin | All in One SEO (AIOSEO) 4.9.10 Lite |

**Relevante Plugins:** `elementor`, `essential-addons-for-elementor-lite`, `happy-elementor-addons`, `turbo-addons-elementor`, `header-footer-elementor`, `header-footer-builder-for-elementor`, `the-events-calendar` 6.17.1, `contact-form-7` 6.1.6, `gdpr-compliant-recaptcha-for-all-forms` 5.0, `accessibility-onetap` 2.13.0, `safety-exit` 7.0.2, `all-in-one-seo-pack`, `wp-members` 3.5.6.

### 2.2 Kritische Defekte im Bestand (die der Relaunch behebt)

1. **Navigation existiert nur clientseitig.** Das Plugin `header-footer-builder-for-elementor` injiziert das Menü per JavaScript aus einem String. Für Crawler und ohne JS gibt es keine Navigation — gleichzeitiger SEO- und Barrierefreiheits-Defekt.
2. **Notausgang funktioniert nur mit JavaScript.** Der Button wird per JS erzeugt, es gibt keinen Tastatur-Shortcut. Für die Zielgruppe zu wenig.
3. **Google Fonts laden ungefragt** von `fonts.googleapis.com` (Kumbh Sans, Playfair Display, aus dem Theme).
4. **Cookie-Banner blockiert nichts.** Plugin von `hu-manity.co`, konfiguriert mit `"blocking":false`. Das Banner lädt selbst von `cdn.hu-manity.co`.
5. **Zwei betterplace-iframes auf `/spenden/`** laden ungefragt von `project-widget.betterplace.org` (Projekte 170775 und 173993) — Drittanbieter-Embed ohne Consent.
6. **Seitengewicht:** Startseite 436 KB HTML für ~479 Wörter Text. Ursache: OneTap-Übersetzungen für 42 Sprachen inline, Elementor-Inline-CSS, Header als JS-String.
7. **Zwei tote Links auf der Startseite:** `/selbsthilfegruppen-2/` (404) und `/kontaktformular/` (404, Linktext „Anfragen & Austausch").
8. **Security-Header fehlen komplett** im Frontend: kein HSTS, keine CSP, kein X-Frame-Options, keine Referrer-Policy.

### 2.3 Barrierefreiheit im Bestand: OneTap

Plugin `accessibility-onetap` v2.13.0, komplett First-Party ausgeliefert. **Datenschutzrechtlich sauber** — keine externen Requests, kein SaaS-Overlay wie UserWay/accessiBe. Einziger Endpunkt ist die eigene `admin-ajax.php`.

Funktionsumfang (Referenz für Funktionsparität):
- *Profile:* Sehbehinderung, Anfallsicherheit, ADHD-freundlich, Blindheit, Epilepsie-sicher
- *Inhalt:* Schriftgröße, Zeilenhöhe, Buchstabenabstand, Schriftstärke, lesbare Schriftart, Dyslexie-Schriftart, Textausrichtung, Textvergrößerung, großer Cursor, Links hervorheben
- *Farbe:* Dunkler/Heller/Hoher Kontrast, Monochrom, Sättigung
- *Orientierung:* **Leselinie**, Lese-Maske, Seite vorlesen, Tastaturnavigation, Bilder ausblenden, Geräusche stummschalten, Titel/Inhalt hervorheben, Animationen stoppen, Zurücksetzen
- *Sonstiges:* Skip-Links, Toolbar ausblenden, 42 Sprachen

### 2.4 Notausgang im Bestand

Plugin `safety-exit` v7.0.2. Verhalten: `window.open('https://www.google.com','_blank')` + `location.replace('https://www.google.com')`. Nur Button (unten rechts, grün), **kein ESC-Shortcut, keine Funktion ohne JS**.

### 2.5 Seitenstruktur (24 Seiten, sitemap.xml, Stand 25.07.2026)

| URL | Thema |
|---|---|
| `/` | Startseite |
| `/verein/` | Vereinsarbeit |
| `/ueber-uns-vorstand-und-team/` | Über uns / Vorstand & Team |
| `/satzung/` | Satzung |
| `/mitgliedschaft/` | Mitgliedschaft |
| `/selbsthilfegruppen/` | Selbsthilfegruppen |
| `/arbeitsgruppen/` | Arbeitsgruppen |
| `/veranstaltungen/` | Veranstaltungen |
| `/anfragen/` | Anfragen (Kontakt-/Anliegenformular) |
| `/kontakt/` | Kontakt |
| `/spenden/` | Spenden |
| `/das-hilfesystem/` | Das Hilfesystem |
| `/unterstuetzung/` | Unterstützung |
| `/fsm-erweitertes-hilfesystem/` | FSM – Erweitertes Hilfesystem |
| `/buerokratie-labyrinth/` | Das Bürokratie-Labyrinth |
| `/kein-einzelfall-im-dialog/` | Kein Einzelfall im Dialog |
| `/trauma-bindung-und-beziehung/` | Trauma, Bindung und Beziehung |
| `/traumafolgestoerungen-verstehen/` | Traumafolgestörungen verstehen |
| `/erwerbsminderungsrente/` | Erwerbsminderungsrente |
| `/istanbul-konvention/` | Istanbul-Konvention |
| `/wissen/` | Wissen |
| `/kinderkodex/` | Kinderkodex |
| `/datenschutz/` | Datenschutz |
| `/impressum-2/` | Impressum (Alt-Slug, → `/impressum/` mit 301) |

Zusätzlich: `/event/selbsthilfegruppe-retraumatisierung-durch-antragsstellung/`

### 2.6 Alte Menü-Hierarchie

Drei Ebenen tief und inhaltlich fragwürdig — wird in der **Navigation** neu geordnet, die **URLs bleiben 1:1**:

```
Startseite · Selbsthilfegruppen · Arbeitsgruppen · Anfragen · Spenden · Kontakt
Unterstützung → Wissen → Erwerbsminderungsrente, FSM
Veranstaltungen → KE!N EINZELFALL im Dialog → Trauma/Bindung, Das Hilfesystem,
                  Traumafolgestörungen, Bürokratie-Labyrinth
Verein → Über uns, Satzung, Istanbul-Konvention, Kinderkodex, Mitgliedschaft
```

### 2.7 Inhaltsvolumen

- **Text gering:** ~16–20 echte Inhaltsseiten. Größte: `/ueber-uns-vorstand-und-team/` (2.441 Wörter), `/datenschutz/` (2.243), `/arbeitsgruppen/` (1.135), `/selbsthilfegruppen/` (914). Rest meist 300–600 Wörter.
- **Blog: 0 Beiträge** (`x-wp-total: 0`), 3 Kategorien. Wird neu aufgebaut, nicht migriert.
- **Events: praktisch ungenutzt.** 1 Event in der Sitemap, 0 kommende Termine. `/veranstaltungen/` ist eine handgebaute Elementor-Seite, kein Kalender-View. iCal-Export unter `/events/?ical=1` sollte erhalten bleiben.
- **~120 PDFs = größter Migrationsposten.** 141 Medien-Anhänge gesamt, davon ~121 Dokumente. Internes Nummernschema (`6.5.3.x` Erwerbsminderung, `6.2.1.x` FSM, `1.5.x` Mitgliedschaft, `4.5.x` Satzung/Kodex). Teils Formulare und amtliche Schriftwechsel — **können extern verlinkt sein, URLs müssen erhalten bleiben oder einzeln per 301 gemappt werden.**

### 2.8 Spenden-Bestand (`/spenden/`)

- **Überweisung:** Deutsche Skatbank, IBAN `DE79 8306 5408 0006 8893 10`, BIC `GENODEF1SLR`
- **PayPal:** `paypal@kein-einzelfall.de` (Donate-Link mit `currency_code=EUR`)
- **betterplace.org:** zwei Projekte (Onlinepräsenz/laufende Kosten, Ausstattung mobile Unterstützung) — aktuell als iframe-Widget eingebunden
- **Spendenbescheinigung:** formlos per E-Mail an `verwaltung@kein-einzelfall.de`

### 2.9 SEO-Baseline (darf sich nicht verschlechtern)

- Title-Muster: `%Seitentitel% - Kein Einzelfall e.V.` (Startseite: `Startseite - Kein Einzelfall e.V. - Opferhilfe`)
- Meta-Descriptions auf allen geprüften Seiten gepflegt, Canonicals selbstreferenzierend, Open Graph vorhanden
- JSON-LD `@graph`: `Organization`, `WebSite`, `WebPage`, `BreadcrumbList`, `ListItem`, `ImageObject`. **Kein** Event-, NGO- oder FAQ-Schema → Verbesserungspotenzial
- Kein hreflang (einsprachig `de-DE`)
- **Kein Analytics im Einsatz** (kein GA, kein GTM) — der Relaunch startet auf sauberer Tracking-Basis
- robots.txt: `Disallow: /wp-admin/`, `/?s=`, `/search/`; AdsBot komplett gesperrt

---

## 3. Leistungsumfang (Angebot AN-268)

| # | Position | Betrag |
|---|----------|--------|
| 1 | Erweiterte Webseite / CMS / Re-Launch (WordPress → Laravel, responsive/Mobile-First, Barrierefreiheit nachbilden) | 7.999,00 € |
| 2 | Event-Kalender mit CMS-Anbindung (Eigenbau, ersetzt „The Events Calendar") | 499,00 € |
| 3 | Dynamischer Blog / News mit CMS-Anbindung (Suchfunktion, Kategorien, Pagination, SEO-Struktur) | 499,00 € |
| 4 | Anbindung Kontaktformular (TLS-verschlüsselt, DSGVO-konforme Verarbeitung sensibler Anfragen) | 299,00 € |
| 5 | Datenbank mit Website-Anbindung (Mitglieder-/Gruppen-/Anfragenverwaltung, Rollen/Rechte) | 1.299,00 € |
| 6 | SEO-Optimierung (Metadaten, Sitemap, Google Business, 301-Redirects) | 499,00 € |
| 7 | DSGVO & Datenschutz-Konzeption (Datenschutzerklärung, Cookie-Consent, AVV mit Hoster) | 349,00 € |
| | **Summe Positionen** | **11.443,00 €** |
| | Neukundenrabatt (5 %) | −572,15 € |
| | Kein-Einzelfall-Rabatt (10 %) | −1.144,30 € |
| | **Gesamtbetrag (netto = brutto, §19 UStG)** | **9.726,55 €** |

**Explizit vereinbart:** Texte und Bilder werden vom Verein gestellt — keine Content-Erstellung durch uns. Der Content der aktuellen Seite wird komplett übernommen und eingepflegt. Design und technische Umsetzung liegen vollständig in unserer Hand („freie Hand").

> ⚠️ **Kalkulationsrisiko:** Die Übernahme von ~20 Bestandsseiten und ~120 PDFs ist erheblicher Aufwand und in keiner Position separat bepreist. Siehe Abschnitt 11.

---

## 4. Tech-Stack (final)

| Thema | Entscheidung | Begründung |
|---|---|---|
| Backend | **Laravel 12** (PHP 8.3+) | Aktuell, Support bis 2027 |
| Datenbank | **PostgreSQL 16** | Konsistent zu bisherigen Projekten. MySQL-Altbestand wird per Import-Skript übernommen |
| Frontend | **Blade + Tailwind 4 + Alpine.js** | Mobile-First |
| Blog-Filter/Suche | **Kein Livewire — serverseitig via GET-Parameter** | Livewire-Filter sind für Crawler unsichtbar und für Screenreader schlechter. Genau der Fehler, den die Altseite mit der JS-Navigation macht. `?kategorie=…&seite=2` ist SEO-sicher, A11y-sicher und cachebar |
| Admin-Panel | **Filament 4** | Schlank, kein Full-CMS. Rollen/Rechte über Policies |
| Rollen/Rechte | `spatie/laravel-permission` | Standard, gut geprüft |
| Mailversand | **SMTP über den eigenen Hoster** | Jeder externe Maildienst wäre ein zusätzlicher Auftragsverarbeiter für Art.-9-Daten. Weniger Dienste = weniger Angriffsfläche, weniger AVV |
| Suche | **PostgreSQL Full-Text (`tsvector`)** | Kein Meilisearch/Algolia — kein weiterer Dienst, kein weiterer AVV |
| Hosting | **VPS in Deutschland** + AVV (Hostinger/Hetzner) | Shared Hosting trägt Laravel mit Queue/Scheduler nicht sinnvoll. *Noch zu entscheiden* |
| Analytics | **keins** (optional self-hosted Matomo, cookiefrei) | Bestand hat kein Tracking — dieser Vorteil sollte nicht leichtfertig aufgegeben werden |

---

## 5. Architektur

### 5.1 Inhaltsseiten: kuratiertes Block-System

Die ~20 Inhaltsseiten liegen als `pages` + `page_blocks` in der DB, **nicht** als feste Blade-Views. Grund: Position 1 verspricht einen CMS-Anteil, und auch im Full-Service-Modell wollen *wir* Texte ändern können, ohne zu deployen.

Aber: **kuratierte Block-Typen, kein freier Page-Builder** — genau das ist der Fehler, den Elementor auf der Altseite macht. Die Block-Typen entsprechen 1:1 den Mockup-Komponenten:

`hero` · `stat_strip` · `quick_access` · `text` · `text_media` · `topic_list` · `event_teaser` · `news_teaser` · `cta_band` · `accordion` · `download_list` (PDFs) · `contact_close`

```
resources/views/
  components/ui/       button, card, badge, chip, eyebrow, section-head, photo, topic-row
  components/blocks/   hero, stat-strip, quick-access, text, cta-band, …  (1:1 zu Block-Typen)
  components/layout/   header, footer, mobile-bar, a11y-toolbar, exit-button, consent
  layouts/app.blade.php
```

### 5.2 Datenmodell

```
users            Admin/Redaktion; Rollen: admin, redaktion, mitgliederverwaltung
pages            slug, title, meta_title, meta_description, noindex, published_at
page_blocks      page_id, type, position, data (jsonb)
navigation_items label, url|page_id, parent_id, position     ← serverseitig gerendert
posts            slug, title, excerpt, body, published_at, category_id, search_vector
categories       slug, name
events           slug, title, starts_at, ends_at, location, type, description, is_online
                 (+ iCal-Export, ersetzt /events/?ical=1)
groups           name, type (selbsthilfe|arbeits), description, schedule, is_open
members          🔒 Art. 9 — verschlüsselte Kontaktfelder
inquiries        🔒 Art. 9 — verschlüsselte Freitext- und Kontaktfelder
media            path, title, mime, size    (~120 PDFs)
redirects        from, to, status (301)
```

**Mitglieder-Login:** aktuell **nicht** im Umfang. Das Schema wird aber so angelegt, dass es später ohne Bruch nachrüstbar ist (`members` mit optionaler `user_id`-Relation, Rollen-Setup von Anfang an vorhanden). Auf der Altseite ist `wp-members` installiert — beim DB-Zugriff prüfen, ob dort echte Accounts existieren.

### 5.3 Umgang mit Art.-9-Daten

- `inquiries` (Freitext, Name, E-Mail) und die Kontaktfelder von `members` als `encrypted` Cast → liegen verschlüsselt in der DB. **Konsequenz: keine SQL-Suche darauf möglich.** Für Anfragen unkritisch; für Mitglieder ggf. ein unverschlüsselter Suchindex nur auf dem Nachnamen.
- **Anfrage-Inhalte werden nie per E-Mail verschickt.** Benachrichtigung an den Verein lautet nur „Es liegt eine neue Anfrage vor" + Link ins Panel. E-Mail ist unverschlüsselter Transport — das ist der wirksamste einzelne Datenschutz-Hebel im Projekt.
- **Anonyme Kontaktaufnahme:** E-Mail-Feld optional, mit Hinweis „ohne Angabe können wir dir nicht antworten".
- **Löschkonzept von Anfang an:** `inquiries` nach X Monaten automatisch löschen (Scheduler). Frist mit dem Verein festlegen.
- **Spam-Schutz ohne Drittdienst:** Honeypot + Zeitfalle + Rate-Limit. Kein reCAPTCHA (die Altseite macht das mit lokalem Proof-of-Work bereits richtig).

---

## 6. Design

**Freigegebenes Mockup:** `KEIN-EINZELFALL_Mockup.html` (im Repo) — Startseite, öffentlich abgestimmt unter https://claude.ai/public/artifacts/6e845b8f-bc12-486d-9fed-9c54c29118d6

### Design-Tokens

| Token | Hex | Verwendung |
|---|---|---|
| `--cream` | `#F3ECDD` | Seitenhintergrund |
| `--cream-deep` | `#EAE1CC` | (definiert, ungenutzt) |
| `--card` | `#FBF7EE` | Karten, Flächen |
| `--ink` | `#2C2620` | Fließtext |
| `--ink-soft` | `#6B6255` | Sekundärtext, Navigation |
| `--green` | `#2E4A3A` | Primärbutton, Akzente, `theme-color` |
| `--green-deep` | `#233A2D` | Footer, CTA-Band, Links |
| `--green-mist` | `#DCE6DB` | Icon-Kacheln, Badges |
| `--line` | `#D9CDB2` | alle Trennlinien |
| — | `#8B3A2B` | einziger Warnton (Mobile-Exit) |
| `--radius` | `14px` | Karten; Buttons `999px`; CTA-Band `20px` |

**Schriften:** Fraunces (Headlines), Source Serif 4 (Body), Caveat (handschriftliche Akzente). Im Mockup noch vom Google-CDN — **müssen self-hosted als woff2 ausgeliefert werden.**

**Tonalität** (aus Instagram `kein_einzelfall_opferhilfe` abgeleitet und im Mockup umgesetzt): warme Beige-/Cremetöne statt klinischem Weiß, dunkles Waldgrün als Akzent, Handschrift für emotionale Kernaussagen, ruhige undramatische Bildsprache. Tiefe entsteht über 1px-Linien, nicht über Schatten. Gesamteindruck: ruhig, warm, seriös, **keinesfalls reißerisch**.

**Sektionen der Startseite:** Header · Hero (2-spaltig, CSS-Steinstapel) · Stat-Strip · Quick-Access (4 Cards) · Intro · Events (3 Cards) · Wissen (6 Topic-Rows) · News (3 Cards) · CTA-Band · Contact-Close · Footer (4 Spalten) · Mobile Sticky-Bar.

### Was das Mockup noch nicht kann (beim Nachbau zwingend zu ergänzen)

- **Keine `@media`-Queries** — Responsivität ist über eine künstliche `.frame.mobile`-Klasse simuliert. Deren Regeln sind die Mobile-First-Basis, die Desktop-Werte gehören hinter `md:`/`lg:`.
- **Alle Größen in `px`** — muss auf `rem` umgerechnet werden, sonst ist ein Schriftgrößenregler technisch unmöglich.
- **Kein `<main>`, kein Skip-Link, 0 ARIA-Attribute, keine `:focus`-Styles.**
- **Kein Burger-/Mobile-Menü** — mobil ist die Navigation im Mockup schlicht nicht erreichbar.
- Alle Buttons ohne Funktion; keine der A11y- oder Notausgang-Funktionen implementiert.
- Logo ist ein Base64-PNG (100×100, rein schwarz auf transparent, kalligrafisches „KE!N"-Monogramm). **Muss als eigenes Asset extrahiert und als SVG nachgezeichnet werden**, plus helle Variante für dunkle Flächen (aktuell per cremefarbenem Kreis-Badge im Footer umgangen).

---

## 7. Barrierefreiheit & Notausgang

### 7.1 Fundament (wichtiger als jedes Widget)

Alle Größen in `rem`, echtes `<main>`, Skip-Links, sichtbare `:focus-visible`-Indikatoren, ARIA nur wo nötig, `aria-current` in der Navigation, `aria-hidden` auf den ~22 dekorativen SVGs, `prefers-reduced-motion` und `prefers-contrast` von Haus aus respektiert. Serverseitig gerenderte Navigation.

### 7.2 Eigene A11y-Toolbar

Schaltet CSS-Custom-Properties und Klassen auf `<html>`. Umfang so weit wie sinnvoll:

Schriftgröße (4 Stufen) · Zeilenhöhe · Buchstabenabstand · Schriftstärke · lesbare Schriftart · Dyslexie-Schriftart · Kontrast (hell/dunkel/hoch/monochrom) · Sättigung · Leselinie · Lesemaske · großer Cursor · Links hervorheben · Titel/Inhalt hervorheben · Bilder ausblenden · Animationen stoppen · Zurücksetzen

Persistenz über `localStorage` — technisch notwendige Funktionalität auf ausdrücklichen Nutzerwunsch, **kein Consent erforderlich**, aber in der Datenschutzerklärung zu erwähnen.

**Bewusst nicht nachgebaut:** Vorlesefunktion (Screenreader und Browser können das besser), 42 Sprachen (Seite ist einsprachig), die „Profile" für Epilepsie/Blindheit/ADHS (Marketing-Etiketten über Funktionen, die wir einzeln ohnehin anbieten — falls der Verein sie namentlich möchte, als Voreinstellungs-Bündel der Einzelfunktionen umsetzbar).

### 7.3 Notausgang — ehrliche Einordnung

**Wirksam:**
- Echtes `<a href="…">` im Server-HTML → funktioniert **auch ohne JavaScript** (die Altseite kann das nicht)
- Mit JS zusätzlich `location.replace()` → ersetzt den aktuellen History-Eintrag
- Tastatur-Shortcut (3× `ESC`), sichtbar erklärt
- Mobil dauerhaft in der Sticky-Bar, ausreichend große Trefferfläche
- `Referrer-Policy: no-referrer` → die Zielseite erfährt nicht, woher der Besuch kam

**Sicherheitstheater — nicht versprechen:**
- `location.replace()` löscht **nur den aktuellen** Eintrag. Alles davor bleibt im Verlauf. Der Browser-Verlauf lässt sich aus einer Webseite heraus nicht löschen — harte Browser-Grenze, kein Implementierungsproblem.
- Tab-Titel-Tarnung hilft nur beim Schulterblick, nicht gegen jemanden, der den Verlauf prüft.

**Konsequenz:** Ein ruhiger, kurzer Hinweis auf der Seite, wie man den Verlauf löscht bzw. ein privates Fenster nutzt. Alles andere wäre ein Sicherheitsversprechen, das wir nicht halten können — bei dieser Zielgruppe wäre das fahrlässig.

---

## 8. SEO & Migration

> Kundenwunsch: „nicht schlechter dastehen als jetzt."

1. **Alle Slugs 1:1 übernehmen.** Die Informationsarchitektur wird in der *Navigation* neu geordnet, nicht in den URLs. Einzige Ausnahme: `/impressum-2/` → `/impressum/` (301).
2. Vor jeder Änderung am Altsystem: Zugriff auf Google Search Console / Analytics sichern, sonst einrichten.
3. Backlink-Check (Search Console → Links).
4. `redirects`-Tabelle + Middleware; **jede** der 24 URLs + Event-URL dokumentiert.
5. **~120 PDF-URLs unter `/wp-content/uploads/…` müssen erhalten bleiben** — Pfad beibehalten oder einzeln per 301 mappen. Teils amtliche Dokumente, die extern verlinkt sein können.
6. Meta-Titel/-Descriptions aus dem Bestand übernehmen (Muster `%Seite% - Kein Einzelfall e.V.`).
7. JSON-LD mindestens im Bestand, ergänzt um `Event` und `NGO`.
8. Security-Header nachrüsten: HSTS, CSP, X-Frame-Options, Referrer-Policy.
9. Die beiden bestehenden 404-Links mitreparieren.
10. Neue sitemap.xml nach Go-Live einreichen; Google Business Profile aktualisieren.
11. 2–4 Wochen engmaschiges Monitoring auf Crawling-Fehler.
12. **Alte Seite erst abschalten, wenn Redirects live und getestet sind** — keine Downtime zwischen alt und neu.

---

## 9. DSGVO & Datenschutz (nicht verhandelbar)

- Hosting-Standort EU/Deutschland, **AVV mit Hoster** abschließen
- **AVV zwischen Nils-Digital und Verein**, sobald wir Zugriff auf Live-/Testdaten mit echten Nutzerdaten haben
- Cookie-Consent mit echtem Opt-in **vor** jedem Tracking/Embed (der Bestand macht das nicht)
- Fonts und alle Assets lokal hosten
- **betterplace-Widgets nur als 2-Klick-Lösung** (aktuell laden sie ungefragt)
- Kontaktformular: TLS-Pflicht, sensible Freitextfelder verschlüsselt in der DB
- Anfrage-Inhalte nie per E-Mail versenden (siehe 5.3)
- Anonyme Kontaktaufnahme ermöglichen
- Löschfristen definiert und automatisiert
- Eigene Datenschutzerklärung passend zu den tatsächlich eingesetzten Tools — **keine Vorlage 1:1**, Abstimmung mit den Anwälten des Vereins

---

## 10. Offene Punkte

### Geklärt (26.07.2026)

- [x] **Farbwelt/Stil** — Tokens liegen im Mockup vor
- [x] **Start vor Anzahlung:** Fundament (Setup, Design-System, Komponenten, A11y, Notausgang) wird vorgezogen. Content-Migration erst nach Anzahlung
- [x] **Barrierefreiheit:** eigene Toolbar, Umfang „so viel wie möglich und sinnvoll" — siehe 7.2
- [x] **Mitglieder-Login:** vorerst **nicht** im Umfang, Schema wird aber nachrüstbar angelegt
- [x] **Spenden:** bleibt Info-Seite mit PayPal-, Überweisungs- und betterplace-Angaben — keine eigene Zahlungsanbindung

### Offen

- [ ] **Hosting final entscheiden** (VPS-Anbieter, laufende Kosten sind nicht im Pauschalpreis enthalten)
- [ ] **Zugriff/Export der bestehenden Datenbank(en)** — Bestand vorhanden, aber laut Verein unstrukturiert („wirr"), vermutlich MySQL. **Vor Aufwandsschätzung für die Bereinigung unbedingt Einsicht nehmen.** Dabei prüfen: existieren in `wp-members` echte Accounts?
- [ ] **Genauer Funktionsumfang der Mitglieder-/Gruppenverwaltung** mit dem Verein bestätigen
- [ ] **Zugriff auf Google Search Console / Analytics** (falls vorhanden) — **vor** Änderungen am Altsystem
- [ ] **Zugriff/Export der WordPress-Inhalte** (Texte, Bilder, die ~120 PDFs)
- [ ] **Logo überarbeiten** — aktuell nur schwarzes Base64-PNG, braucht Vektor-Version + helle Variante
- [ ] **Löschfrist für Anfragen** mit dem Verein festlegen
- [ ] **AGB von Nils-Digital** an dieses Projekt anpassen (vor Vertragsabschluss)
- [ ] **Pflegemodell/Retainer** als separates Folgeangebot vorbereiten

---

## 11. Risiken

**1. Das Budget ist für den Umfang knapp.** 9.726 € entsprechen bei realistischem Stundensatz grob 130–150 Stunden. Die Meilensteine (Abschnitt 12) landen eher bei 35–50 Tagen. WCAG-Anspruch, Block-CMS, Mitgliederverwaltung und ~120 PDFs sind zusammen mehr, als da drin ist.
→ *Gegenmaßnahme:* Scope von Position 5 festzurren, **Content-Migration als eigene Position ausweisen.** „Keine Content-Erstellung" heißt nicht „kein Aufwand".

**2. Die Alt-Datenbank ist unbekannt.** „Wirr", kein Zugriff, Umfang unklar — dagegen stehen 1.299 € pauschal.
→ *Gegenmaßnahme:* **Vor Angebotsannahme Einsicht nehmen. Nachtragsvorbehalt für die Datenbereinigung wieder ins Angebot aufnehmen** (war im ursprünglichen Plan vorgesehen). Größtes kaufmännisches Risiko im Projekt.

**3. Rechtlich: Art.-9-Daten.** Bei Verarbeitungszwecken, Aufbewahrungsfristen, Datenschutzerklärung und AVV **nicht selbst raten** — der Verein stellt ausdrücklich eigene Anwälte zur Verfügung. Diesen Rückkanal aktiv nutzen.

**4. Barrierefreiheit ohne definierten Maßstab.** „Maximal" und „gleichwertig oder besser" sind nicht abnahmefähig.
→ *Gegenmaßnahme:* **WCAG 2.1 AA vertraglich zusichern**, AAA als Ziel ohne Zusage. Prüfverfahren vereinbaren (axe-core + Lighthouse in CI, manueller Screenreader-Test, Tastatur-Durchlauf). Sonst wird „ist das barrierefrei genug?" eine endlose Abnahme-Diskussion.

**5. Kein Zugriff auf Search Console.** Ohne Baseline lässt sich nach dem Launch nicht belegen, dass sich SEO nicht verschlechtert hat — und genau das ist ausdrücklicher Kundenwunsch.
→ *Gegenmaßnahme:* Zugriff sichern, **bevor** am Altsystem etwas geändert wird.

**6. Erwartungsmanagement Notausgang.** Der Browser-Verlauf lässt sich nicht löschen (siehe 7.3). Wird das gegenüber dem Verein nicht klar kommuniziert, entsteht ein Sicherheitsversprechen, das niemand halten kann.

---

## 12. Meilensteine

| # | Inhalt | grob |
|---|---|---|
| 0 | **Vorarbeit ohne Code:** DB-Zugriff und WP-Export anfordern, Search-Console sichern, offene Punkte klären | — |
| 1 | Setup: PHP 8.3/Composer, Laravel 12, PostgreSQL, Tailwind, CI | 1 Tag |
| 2 | Design-System: Tokens aus dem Mockup, **px → rem**, self-hosted Fonts, UI-Komponenten | 3–4 Tage |
| 3 | Layout + serverseitige Navigation + Footer (mit nils-digital.de) + **Mobile-Menü** | 2 Tage |
| 4 | **A11y-Toolbar + Notausgang** — früh, weil es alles andere beeinflusst | 3–4 Tage |
| 5 | Startseite aus Block-Komponenten | 2–3 Tage |
| 6 | Filament-Panel, Rollen, Pages/Blocks-Verwaltung | 4–5 Tage |
| 7 | Blog + Events (inkl. iCal) + Suche | 4 Tage |
| 8 | Kontakt/Anfragen: Verschlüsselung, Löschkonzept, Spam-Schutz | 2–3 Tage |
| 9 | Mitglieder-/Gruppenverwaltung (ohne Login, nachrüstbar) | 3–5 Tage |
| 10 | Content-Migration: 20 Seiten + ~120 PDFs | 4–6 Tage |
| 11 | Consent-Banner, Datenschutzerklärung, Security-Header | 2–3 Tage |
| 12 | A11y-Audit, Redirect-Tests, Go-Live, 2–4 Wochen Monitoring | 3 Tage |

---

## 13. Nächste Schritte

1. Angebot AN-268 anpassen: Content-Migration als eigene Position, Nachtragsvorbehalt Datenbereinigung, WCAG 2.1 AA als Maßstab
2. Nach Zusage: 50 % Anzahlung anfordern, angepasste AGB beilegen
3. Kickoff mit dem Verein: offene Punkte aus Abschnitt 10 klären
4. **URL-Export & Search-Console-Zugriff sichern, bevor am Altsystem etwas geändert wird**
5. Fundament-Entwicklung starten (Meilensteine 1–5)

---

*Stand: 26.07.2026. Technische Bestandsaufnahme in Abschnitt 2 verifiziert durch direkte Analyse der Live-Seite.*
