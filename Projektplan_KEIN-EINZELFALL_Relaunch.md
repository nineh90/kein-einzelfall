# Projektplan: Relaunch KE!N EINZELFALL e.V.

**Angebot:** AN-268
**Kunde:** KE!N EINZELFALL e.V. (Opferhilfe-Verein, Hamburg, gegründet 2024)
**Ansprechpartnerin:** Tatjana Belmar
**Aktuelle Seite:** https://kein-einzelfall.de/ (WordPress + Elementor)
**Ziel:** Individuelles Redesign + Umstellung auf Laravel, inkl. Datenbank-Anbindung
**Budget (netto/brutto, §19 UStG):** 9.726,55 EUR (Summe Positionen 11.443,00 € − 15 % Rabatt: 5 % Neukunde + 10 % Kein-Einzelfall-Rabatt). Anzahlung 50 % = 4.863,28 €.
**Zahlungsbedingung:** 50 % Anzahlung bei Auftragsbestätigung, Rest nach Abschluss. Es gelten die AGB der Nils-Digital.
**Deadline:** Keine feste Deadline. Auftraggeberin ist bewusst, dass ihre zahlreichen Wünsche nicht kurzfristig umsetzbar sind — Projekt läuft phasenweise, mit vielen erwarteten Folgeaufträgen. **Start: voraussichtlich September 2026**, sobald die Fördergelder des Vereins freigeschaltet sind. Die Wartezeit bis dahin sollte für die Klärung der offenen Punkte (Abschnitt 9) genutzt werden.
**Langfristige Ausrichtung:** Auftraggeberin möchte sich selbst dauerhaft nicht mit Website-Pflege oder Social Media befassen — spricht für ein Full-Service-Modell (Pflege, Content-Einpflege, ggf. Social-Media-Betreuung durch Nils-Digital) statt reiner Übergabe an ein Kunden-CMS. Relevant für Position "Pflege nach Projektabschluss" (Abschnitt 9 des ursprünglichen Fragebogens) und für spätere Folgeaufträge/Wartungsvertrag.

---

## 1. Projektkontext

Der Verein bietet eine Austausch- und Informationsplattform für Opfer/Mit-Opfer von Straftaten, Angehörige, Interessierte und Fachpersonen. Themenfelder u.a. Soziales Entschädigungsrecht (OEG/SGB XIV), Schwerbehindertenausweis, Pflegegrad. Der Verein organisiert Selbsthilfegruppen und Arbeitsgruppen, nimmt Mitgliedschaften und Spenden entgegen.

**Wichtig für die gesamte Umsetzung:**
- Die Zielgruppe befindet sich teils in belastenden/schutzbedürftigen Situationen. Klarheit, Ruhe und schnelle Erreichbarkeit der Kernfunktionen (Kontakt, ggf. "Notausgang") sind kein Nice-to-have, sondern zentral.
- Es werden teils **besonders schützenswerte personenbezogene Daten** verarbeitet (Gesundheitsdaten, Opferstatus). DSGVO-Sorgfalt hat oberste Priorität, auch über das gesetzliche Minimum hinaus.
- Dieses Projekt ist ein Referenzprojekt für Nils-Digital — Qualität und Sorgfalt haben Vorrang vor Tempo.

> **Ressourcen-Hinweis:** Der Verein stellt bei Bedarf Zugang zu eigenen Anwälten/juristischem Fachwissen sowie Arbeitsmaterialien zur Verfügung. Bei Unsicherheiten — insbesondere zu Datenschutzerklärung, Verarbeitungszwecken sensibler Daten oder Vertragsfragen (AVV) — sollte dieser Rückkanal aktiv genutzt werden, statt intern zu raten. Ansprechpartnerin: Auftraggeberin (Kontakt vermittelt bei Bedarf).

---

## 2. Ausgangslage (bestehende Seite)

- CMS: WordPress mit Elementor-Pagebuilder
- Vorhandenes Feature: Accessibility-Widget "OneTap" (u.a. Modus für Sehbehinderte, ADHS-freundlicher Modus, Schriftgröße, Kontrast, Leselinie etc.)
- Vorhandenes Feature: "Notausgang"-Link (Sofort-Verlassen-Funktion)
- Aktuelle Hauptseiten/Struktur (verifiziert über sitemap.xml, Stand 25.07.2026 — 24 Seiten total):

| URL | Thema |
|---|---|
| `/` | Startseite |
| `/verein/` | Vereinsarbeit |
| `/ueber-uns-vorstand-und-team/` | Über uns / Vorstand & Team |
| `/satzung/` | Satzung |
| `/mitgliedschaft/` | Mitgliedschaft |
| `/selbsthilfegruppen/` | Selbsthilfegruppen |
| `/arbeitsgruppen/` | Arbeitsgruppen |
| `/veranstaltungen/` | Veranstaltungen (läuft über "The Events Calendar"-Plugin!) |
| `/anfragen/` | Anfragen (Kontakt-/Anliegenformular) |
| `/kontakt/` | Kontakt |
| `/spenden/` | Spenden (inkl. QR-Code Skatkonto) |
| `/das-hilfesystem/` | Das Hilfesystem |
| `/unterstuetzung/` | Unterstützung |
| `/fsm-erweitertes-hilfesystem/` | FSM – Erweitertes Hilfesystem (Fonds Sexueller Missbrauch) |
| `/buerokratie-labyrinth/` | Das Bürokratie-Labyrinth |
| `/kein-einzelfall-im-dialog/` | Kein Einzelfall im Dialog |
| `/trauma-bindung-und-beziehung/` | Trauma, Bindung und Beziehung |
| `/traumafolgestoerungen-verstehen/` | Traumafolgestörungen verstehen |
| `/erwerbsminderungsrente/` | Erwerbsminderungsrente |
| `/istanbul-konvention/` | Istanbul-Konvention |
| `/wissen/` | Wissen |
| `/kinderkodex/` | Kinderkodex |
| `/datenschutz/` | Datenschutz |
| `/impressum-2/` | Impressum |

**Blog:** Nur 2 Einträge im post-sitemap (`/vereins-news/`, `/hello-world-6/` — letzterer ein WP-Standard-Dummy-Post, vermutlich kein echter Content). Blog wird also praktisch neu aufgebaut, nicht migriert.

**Wichtig:** `/veranstaltungen/` läuft aktuell über das WordPress-Plugin "The Events Calendar" (tribe_events) — bei der Migration muss geprüft werden, ob/wie viele Events dort aktuell gepflegt sind, die übernommen werden müssen.

---

## 3. Leistungsumfang (gemäß finalem Angebot AN-268)

| # | Position | Betrag |
|---|----------|--------|
| 1 | Erweiterte Webseite / CMS / Re-Launch (WordPress → Laravel, responsive/Mobile-First, Barrierefreiheit nachbilden) | 7.999,00 € |
| 2 | Event-Kalender mit CMS-Anbindung | 499,00 € |
| 3 | Dynamischer Blog / News mit CMS-Anbindung (Suchfunktion, Kategorien, Pagination, SEO-Struktur) | 499,00 € |
| 4 | Anbindung Kontaktformular (TLS-verschlüsselt, DSGVO-konforme Verarbeitung sensibler Anfragen) | 299,00 € |
| 5 | Datenbank mit Website-Anbindung (Mitglieder-/Gruppen-/Anfragenverwaltung, Rollen/Rechte) | 1.299,00 € |
| 6 | SEO-Optimierung (Metadaten, Sitemap, Google Business, 301-Redirects) | 499,00 € |
| 7 | DSGVO & Datenschutz-Konzeption (Datenschutzerklärung, Cookie-Consent, AVV mit Hoster) | 349,00 € |
| | **Summe Positionen** | **11.443,00 €** |
| | Neukundenrabatt (5 %) | -572,15 € |
| | Kein-Einzelfall-Rabatt (10 %) | -1.144,30 € |
| | **Gesamtbetrag (netto = brutto, §19 UStG)** | **9.726,55 €** |

**Explizit vereinbart:** Texte und Bilder werden vom Verein gestellt und nur geprüft/eingepflegt — keine Content-Erstellung durch uns. Design und technische Umsetzung liegen vollständig in unserer Hand ("freie Hand").

---

## 4. Tech-Stack (Vorschlag, mit Kevin final abzustimmen)

- **Backend:** Laravel (aktuelle LTS-Version)
- **Datenbank:** MySQL oder PostgreSQL (Empfehlung: PostgreSQL, konsistent zu bisherigen Projekten)
- **Frontend:** Blade + Tailwind CSS (Mobile-First), ggf. Livewire/Alpine.js für interaktive Komponenten (Event-Kalender, Blog-Suche/Filter)
- **CMS-Anteil:** Eigenes schlankes Admin-Panel (kein Full-CMS wie WordPress nötig) für Blog, Events, Kontaktanfragen-Verwaltung, Mitgliederverwaltung
- **Hosting:** EU/Deutschland, AVV mit Hoster erforderlich
- **Mailversand:** DSGVO-konformer Anbieter (kein reiner US-Dienst ohne AVV/SCC-Absicherung)

---

## 5. Datenbank – grober Entwurf (finaler Funktionsumfang mit Verein noch abzustimmen)

> **Wichtige Änderung:** Der Verein hat bereits bestehende Datenbank(en) im Einsatz — Rückmeldung: **Bestand ist vorhanden, aber vermutlich unübersichtlich/unstrukturiert ("wirr")**. Das Projekt beginnt daher **nicht** bei null, aber auch nicht bei einer sauberen Basis:
> 1. Zuerst Bestandsaufnahme: Welches System, welche Struktur, welcher Datenumfang liegt aktuell vor?
> 2. **Datenbereinigung als eigener Arbeitsschritt einplanen** — Dubletten, inkonsistente Felder, veraltete Einträge sind zu erwarten. Sollte im Zeitplan/Aufwand nicht "mitgedacht", sondern explizit eingepreist werden, sobald der Umfang bekannt ist (ggf. Nachtrag zum Angebot nötig, falls Aufwand erheblich über Position 5 hinausgeht)
> 3. Klären, ob Migration in neue Struktur, oder Anbindung/Schnittstelle zur bestehenden DB gewünscht ist — bei "wirrem" Bestand spricht viel für eine saubere Neu-Migration statt Anbindung an das Altsystem
> 4. Erst danach Schema-Entwurf final festlegen — die folgende Liste ist ein erster Vorschlag, keine Festlegung

Mögliche Kernentitäten (vorbehaltlich Bestandsaufnahme):

- **users** (Admin/Redakteure, intern)
- **members** (Vereinsmitglieder: Name, Kontakt, Mitgliedsstatus, Beitragsdaten) — hohe Schutzbedürftigkeit
- **groups** (Selbsthilfegruppen / Arbeitsgruppen: Name, Beschreibung, Typ, Termine)
- **group_registrations** (Anmeldungen zu Gruppen, ggf. anonymisierbar)
- **inquiries** (Kontaktanfragen aus Formular — ggf. mit Verschlüsselung sensibler Freitextfelder)
- **events** (Termine für Event-Kalender)
- **blog_posts** (+ categories, tags)
- **donations** (falls Spendenprozess technisch abgebildet werden soll — noch zu klären, ob nur Info-Seite oder echte Verarbeitung)

> **Offene Frage an den Verein:** Soll die Datenbank primär Mitgliederverwaltung, Gruppen-Anmeldungen, Anfragen-Backend oder eine Kombination abbilden? Aktuell nicht final geklärt — vor Entwicklungsstart mit Kunde bestätigen.

---

## 6. Design

**Farb-/Stilwelt (abgeleitet aus Instagram `kein_einzelfall_opferhilfe`):**
- Hintergrund: warme Beige-/Creme-/Sandtöne (ruhig, nicht klinisch-weiß)
- Akzentfarbe: dunkles Waldgrün (Unterstreichungen, Hervorhebungen, CTA-Elemente)
- Text: großteils Schwarz/Dunkelbraun, teils in lockerer Handschrift-Optik für emotionale Kernaussagen ("Ich bin da...", "Gemeinsam wachsen wir weiter...")
- Bildsprache: ruhige, warme Fotografie (Wartezimmer, Alltagsszenen, Naturmotive wie gestapelte Steine) — bewusst undramatisch, kein Stock-Foto-Look
- Wiederkehrendes Vereins-Icon (stilisierter Zaun/Gitter-Schriftzug "KE!N EINZELFALL e.V.") wird als Wasserzeichen/Signatur auf Postings verwendet — auf der Website als Footer-/Signatur-Element denkbar
- Gesamteindruck: ruhig, warm, seriös, keinesfalls reißerisch — Design muss diese Tonalität 1:1 auf der Website weiterführen

> Farbwerte (Hex-Codes) sollten aus den Original-Bilddateien exakt gepickt werden (Kevin/Design), sobald hochauflösendes Material vorliegt — aktuell nur über Screenshots verfügbar.

**Design-Mockup (Entwurf, Startseite):** https://claude.ai/public/artifacts/6e845b8f-bc12-486d-9fed-9c54c29118d6
Öffentlich zugänglich — kann direkt zur Abstimmung mit dem Verein gezeigt werden, jederzeit weiter anpassbar. Enthält Desktop-/Smartphone-Umschalter, Farb-/Typografie-Grundlage sowie mobile Sticky-Bar mit Notausgang.

- Bestehendes Logo wird übernommen (aktuell: `Logo-1.jpg` im WP-Upload-Ordner sichtbar) — im Mockup bereits eingebunden (direkt von Live-URL geladen), reicht für die Design-Abstimmung mit dem Verein. **Für die finale Webseite muss das Logo noch überarbeitet werden** (Auflösung/Vektor-Format, ggf. Nacharbeit) — kein finaler Asset-Stand.
- Design- und Umsetzungshoheit liegt vollständig bei Nils-Digital.
- **Pflicht:** Mobile-First, da Zielgruppe vermutlich überwiegend mobil und in akuten Situationen zugreift.
- Bestehende Barrierefreiheits-Funktionalität (Kontrast, Schriftgröße, Leselinie, Notausgang) muss im neuen System gleichwertig oder besser abgebildet werden.

---

## 7. SEO & Migration (kritischer Punkt laut Kundenwunsch: "nicht schlechter dastehen als jetzt")

> **Status:** Vollständige URL-Liste liegt bereits vor (siehe Abschnitt 2, 24 Seiten). Redirect-Mapping kann darauf direkt aufgebaut werden, sobald neue URL-Struktur feststeht.

1. Vor Umzug: Zugriff auf Google Search Console / Analytics sichern (falls vorhanden), sonst einrichten
2. ~~Vollständige URL-Liste der Altseite exportieren~~ ✅ erledigt (s. Abschnitt 2)
3. Backlink-Check (Search Console → Links)
4. 301-Redirect-Mapping: **jede** der 24 bestehenden URLs → neue URL (1:1-Zuordnung dokumentieren, bevor Altseite abgeschaltet wird)
5. Meta-Titel/-Descriptions der wichtigsten Seiten übernehmen/verbessern
6. Neue Sitemap.xml bei Search Console einreichen nach Go-Live
7. Google Business Profile: Website-Link nach Go-Live aktualisieren
8. Monitoring: 2–4 Wochen nach Launch engmaschig auf Crawling-Fehler prüfen
9. Alte Seite erst abschalten, wenn Redirects live und getestet sind — keine Downtime zwischen alt/neu

---

## 8. DSGVO & Datenschutz (nicht verhandelbar bei diesem Kunden)

- Hosting-Standort EU/Deutschland, AVV mit Hoster abschließen
- Cookie-Consent-Lösung (opt-in vor jeglichem Tracking/Embed)
- Google Fonts & Assets lokal hosten (kein External-CDN-Tracking)
- YouTube/Social-Embeds nur mit Klick-Schutz (2-Klick-Lösung)
- Kontaktformular: TLS-Pflicht, Prüfung ob sensible Freitextfelder zusätzlich verschlüsselt gespeichert werden müssen
- Prüfen: Möglichkeit zur anonymen Kontaktaufnahme sinnvoll?
- Neue Datenschutzerklärung passend zu tatsächlich eingesetzten Tools (keine 1:1-Vorlage)
- AVV zwischen Nils-Digital und Verein, sofern wir Zugriff auf Live-/Testdaten mit echten Nutzerinformationen haben

---

## 9. Offene Punkte / Fragen an den Verein (vor Entwicklungsstart klären)

- [x] Farbwelt/Stil bekannt — Farbwerte wurden vom Verein bereits übermittelt
- [ ] **Zugriff/Export der bestehenden Datenbank(en):** Bestand vorhanden, aber vermutlich unstrukturiert — welches System, welche Struktur, wie viele/welche Datensätze wirklich relevant? Vor Aufwandsschätzung für Bereinigung unbedingt Einsicht nehmen
- [ ] Genauer Funktionsumfang der neuen/erweiterten Datenbank (Mitglieder? Gruppen-Anmeldung? Anfragen-Backend? Kombination?)
- [ ] Zugriff auf bestehende Google Search Console / Analytics (falls vorhanden)
- [ ] Zugriff/Export bestehender WordPress-Inhalte (Texte, Bilder, Struktur)
- [ ] Logo für finale Webseite überarbeiten (aktuelle Version nur Übergangslösung fürs Mockup, s. Abschnitt 6)
- [ ] Erwartung an Barrierefreiheit: gleichwertig zum bestehenden OneTap-Widget oder eigene Lösung akzeptiert?
- [ ] Soll der Spendenprozess technisch abgebildet werden (Zahlungsanbindung) oder bleibt es eine Info-Seite mit externem Link?
- [ ] Bestehende AGB von Nils-Digital: Überarbeitung/Anpassung an dieses Projekt vor Vertragsabschluss nötig
- [ ] **Pflegemodell final klären:** Da Verein sich langfristig nicht selbst um Website/Social Media kümmern möchte — Wartungsvertrag/Retainer als separates (Folge-)Angebot vorbereiten (Content-Pflege, Updates, ggf. Social-Media-Betreuung)

---

## 10. Nächste Schritte

1. Angebot AN-268 final anpassen (Positionsbeschreibungen, DSGVO-Position ergänzen) und an Verein senden
2. Nach Zusage: 50 % Anzahlung anfordern, AGB verlinken/beilegen
3. Kickoff-Termin mit Verein: offene Punkte aus Abschnitt 9 klären
4. URL-Export & Search-Console-Zugriff sichern, bevor am Altsystem etwas geändert wird
5. Technisches Setup (Laravel-Projekt, DB-Schema final, Hosting/AVV) durch Kevin
6. Design-Entwurf auf Basis Social-Media-Farbwelt (sobald vorliegend)

---

*Stand: Entwurf zur internen Weitergabe an Kevin — Ergänzungen/Korrekturen bitte direkt in diesem Dokument.*
