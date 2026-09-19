# Abgleich: Vereinswünsche ↔ aktueller Projektstand

**Grundlage:**
- „Website Kein Einzelfall Inhalt und Struktur" (Verein, 16 Kategorien + Grundsatzwünsche)
- Gemini-Protokoll der Relaunch-Besprechung vom **02.08.2026** (Nils Nehring, Kevin Herrmann,
  Tatjana Belmar, Franziska Künstler)

**Stand des Projekts:** Meilensteine 1–9 fertig, 25 Seiten, 10 Gruppen, 26 Weiterleitungen,
202 Tests. Grundlage für diesen Abgleich ist der Code, nicht der Projektplan.

*Erstellt: 05.08.2026*

---

## 0. Kurzfassung

Von den 16 Wunsch-Kategorien des Vereins sind **7 vollständig oder weitgehend gebaut**,
**4 teilweise**, **5 gar nicht**. Dazu kommen aus der Besprechung sieben Wünsche, die im
Strukturpapier noch nicht standen (Trigger-Warnung, Glossar, KI-Suche, Spenden-Popup,
Kooperationslogos, Video-Archiv, Sunflower).

Wichtiger als die Zahl: **Das Fundament trägt fast alles davon.** Block-System,
Mehrsprachigkeit, Gruppen-/Event-Modell, Downloads und das Filament-Panel sind so gebaut,
dass die meisten neuen Kategorien *Inhalt* sind und kein neuer Code. Echter Neubau sind
im Kern vier Dinge: **Trigger-Warnung, Wissensdatenbank/Bibliothek, Anmeldefunktion,
Mitgliederbereich.**

Der ehrliche Punkt vorweg: **Ein erheblicher Teil dieser Liste steht in keiner Position
von AN-268.** Das ist keine Kritik am Verein — die Wünsche sind legitim und größtenteils
neu. Aber sie gehören als Folgeauftrag bepreist, nicht stillschweigend mitgemacht.
Siehe Abschnitt 6.

---

## 1. Fragen aus der Besprechung, die jetzt beantwortbar sind

Das sind offene „Nächste Schritte" aus dem Protokoll, für die die Antwort bereits im Code liegt:

| Frage | Antwort |
|---|---|
| **„Was machen wir mit dem Grün-Ton?"** | **Erledigt.** Die CI-Farbe `#009640` steht als `--color-green-brand` und wird für Logo, Icons und Zierlinien weiter verwendet. Für Text und Buttons gibt es zwei abgedunkelte Varianten (`#00702F`, `#005725`), die den WCAG-Kontrast erfüllen. Die Marke bleibt erkennbar, die Lesbarkeit stimmt. |
| **„Schriftart der Schreibschrift mitteilen"** (Aktion Nils) | **Caveat** (Google Fonts, OFL-Lizenz, in Canva verfügbar). Dazu: **Fraunces** für Überschriften, **Source Serif 4** für Fließtext, **Literata** für kyrillische Überschriften. Alle self-hosted unter `public/fonts/`, kein Google-CDN. |
| **„Welche Bildformate braucht ihr?"** | Siehe Abschnitt 7 — die Antwort gehört an den Verein zurück. |
| **„Russisch vorbereiten, nicht freischalten"** | **Zurückgenommen (19.09.2026).** War so gebaut (Sprachzeile inaktiv, Demo-Übersetzung maschinell). Kevin hat Russisch gestrichen, bevor irgendjemand daran gearbeitet hat — niemand im Team konnte es gegenlesen. Das Grundgerüst bleibt mehrsprachig; kommt Russisch wirklich, legt der Verein es im Panel an, mit geprüften Texten. |
| **„Blog und Vereinsnews zusammenführen"** | **Erledigt.** Ein Feed unter `/aktuelles`, mit Kategorien und Suche über GET-Parameter. |
| **„Exit-Button immer an gleicher Stelle"** | **Erledigt und besser als im Bestand:** echtes `<a>` im Server-HTML (funktioniert ohne JavaScript), zusätzlich 3× ESC, mobil im Kopfbereich. |

---

## 2. Deckungsgrad der 16 Kategorien

Legende: ✅ steht · 🟡 teilweise · ❌ fehlt

### 1. Der Verein

| Wunsch | Stand | Anmerkung |
|---|---|---|
| 1.1 Über uns / Vereins-Bio | ✅ | `/ueber-uns-vorstand-und-team` |
| 1.2 Unser Team | ✅ | `team_grid`-Baustein, `team_members`-Tabelle, im Panel pflegbar |
| 1.3 Satzung **und Geschäftsordnung** | 🟡 | `/satzung` steht; Geschäftsordnung als Dokument fehlt — reine Inhaltsfrage |
| 1.4 Mitglied werden (Antrag, Ausfüllhilfe, Beitragsordnung, Betreuung) | 🟡 | `/mitgliedschaft` + `download_list` vorhanden; welche der 121 Dokumente dort hingehören, muss der Verein sagen |
| 1.5 **Schutzkonzept** | ❌ | Neue Seite. Reiner Inhalt, kein Code |
| 1.6 **Beschwerdemanagement** | ❌ | Neue Seite + eigener Kontaktweg. Prüfen, ob das ein *eigenes* Formular braucht (Beschwerden über den Verein sollten nicht im normalen Anfragen-Postfach landen) |
| 1.7 Istanbul-Konvention | ✅ | |
| 1.8 Kinderkodex | ✅ | |
| 1.9 **Kooperationen / Netzwerke** | ❌ | Aktion Mensch, Paritätischer, ANUAS. Braucht einen neuen Baustein `partner_logos` (Logo-Raster mit Link + Alternativtext) |
| 1.10 **Schirmherrschaften** | ❌ | Laut Protokoll erst, wenn die Seite steht — Platzhalterstruktur reicht |
| 1.11 **Botschafter** | ❌ | dito. Technisch identisch zu 1.10, ein Baustein deckt beides |

### 2. Selbsthilfegruppen

| Wunsch | Stand | Anmerkung |
|---|---|---|
| Gruppenübersicht + Detailseiten | ✅ | `groups`-Tabelle mit Rhythmus, Wiederholung, Status, Online/Ort |
| Die fünf genannten Gruppen | 🟡 | Vier gepflegt (Bürokratie-Labyrinth, Seelenfarben, Wir sind nicht mehr stumm, Schreibwerkstatt). **„Killing me Softly — Umgang mit Trigger und Skills" fehlt** |
| **Anmeldefunktion** | ❌ | Aktuell nur `anmeldung_hinweis` (Text) und bei Events eine externe `anmeldung_url`. Eine echte Anmeldung ist Neubau — siehe Abschnitt 4 |
| **Teilnahmevereinbarung + Gruppenregeln mit Zustimmung** | ❌ | Hängt an der Anmeldung. Die Zustimmung muss dokumentiert und nachweisbar sein |

### 3. Arbeitsgruppen

| Wunsch | Stand |
|---|---|
| AG 1–6 | ✅ alle sechs gepflegt |
| **AG 7 „Traumabegleiter"-App** | ❌ fehlt als Datensatz — ein Eintrag im Panel |
| Anmeldefunktion + Regeln | ❌ wie bei 2. |

### 4. Veranstaltungen

| Wunsch | Stand | Anmerkung |
|---|---|---|
| 4.1 Anstehende Veranstaltungen | ✅ | `/veranstaltungen`, eigener Kalender statt „The Events Calendar" |
| 4.2 Vergangene Veranstaltungen | ✅ | **Korrektur zur ersten Fassung dieses Dokuments:** Das ist bereits gebaut — `?zeitraum=vergangen`, mit Umschalter und Zählern in `EventController` und `events/index.blade.php` |
| 4.3 Veranstaltungskalender inkl. Gruppen | 🟡 | Kalender steht, iCal-Export auch. Ob Gruppentermine automatisch mitlaufen, ist zu prüfen |
| Anmeldefunktion je Veranstaltung | ❌ | siehe Abschnitt 4 |
| **24/7 Online-WG mit internem Kalender** | ❌ | Setzt den Mitgliederbereich voraus. Der Wunsch „Termine per Mail vorschlagen, nicht öffentlich sichtbar" heißt: zweiter, geschützter Kalender. Eigenes Projekt |

### 5. Projekte (laufend / abgeschlossen)

❌ Fehlt komplett. Aber: **im Grunde derselbe Typ wie Gruppen** — Übersicht, Detailseite,
Status. Entweder ein neues Modell `projects` oder ein dritter `typ` in `groups`.
Empfehlung: eigenes Modell, sonst wird `groups` zum Sammelbecken.

### 6. Unterstützung

| Wunsch | Stand | Anmerkung |
|---|---|---|
| 6.1 Wissen | ✅ | `/wissen` + Themenseiten |
| 6.2 **Anträge und Formulare** (OEG, SER, GdB, Pflegegrad, Persönliches Budget) | 🟡 | Struktur da, aber: In der Besprechung wurde entschieden, **Formulare nicht selbst zu hosten, sondern auf die Behördenseiten zu verlinken.** Der `download_list`-Baustein kann nur lokale Dateien → **braucht eine Erweiterung um externe Links** (mit Hinweis „führt zu einer fremden Seite"). Überschaubar |
| 6.3 **Erfahrungsberichte** | ❌ | Neu. Datenschutz-heikel: Berichte Betroffener sind Art.-9-Daten, selbst wenn sie freiwillig kommen. Braucht eine schriftliche Einwilligung je Bericht und ein Widerrufs-/Löschverfahren |

### 7. Förderung und Spenden

| Wunsch | Stand | Anmerkung |
|---|---|---|
| 7.1 **Förderungen / Stiftungen** | ❌ | Deckt sich mit 1.9 — derselbe Logo-Baustein |
| 7.2 Spendenmöglichkeiten | ✅ | `donation_options`-Baustein: Konto, PayPal, Betterplace (Betterplace als 2-Klick-Lösung, lädt nicht ungefragt) |
| **QR-Code für Bankverbindung** | ✅ | Wunsch von Franziska. EPC-QR-Code („Girocode") wird lokal erzeugt, kein externer Dienst — im `donation_options`-Baustein, seit KEV-10 auch auf der Startseite |
| **Spendenbescheinigung anfordern** | 🟡 | Steht als E-Mail-Adresse drin; ein eigener kleiner Formularweg wäre besser |
| **Spenden auf der Startseite verankern** | ✅ | KEV-10 (19.09.2026): `donation_options` kompakt auf der Startseite — Konto mit QR-Code, PayPal, Verweis auf die vollständige Seite. Das CTA-Band bleibt darunter |
| **Spenden mobil dauerhaft sichtbar** | ❌ | Die mobile Leiste hat drei Einträge (Start, Gruppen, Anfrage). Spenden fehlt — bewusst, weil Eindeutigkeit vorging. **Muss mit dem Verein entschieden werden** |
| **Spenden-Popup ab 5–10 Seitenaufrufen** | ✅ | KEV-6 (19.09.2026): Kasten unten am Rand ab dem 5. Seitenaufruf, 30 Tage Ruhe nach dem Wegklicken, nie auf Spenden/Anfragen/Kontakt/Fehlerseiten. Zahlen in `config/spendenhinweis.php`, vom Verein zu bestätigen |

### 8. Kontakt

✅ `/kontakt` und `/anfragen` mit verschlüsseltem Formular, Honeypot statt reCAPTCHA,
Anfrage-Inhalte werden nie per E-Mail verschickt. **Der Wunsch „alle E-Mail-Adressen und
Bereiche mit Kontaktformular" ist teilweise offen** — aktuell ein Formular; ob es je Bereich
eines braucht (Beschwerde, Veranstaltung, Presse), ist zu klären.

### 9. Archiv → **„Bibliothek" / „Wissensdatenbank"**

❌ **Der größte fehlende Block.** Urteile, Gutachten, Gesetze, Infopool von Anwälten,
Folgestörungen. Laut Protokoll ausdrücklich umbenannt, weil „Archiv" nach Staub klingt.

Das ist kein Blocktyp, sondern ein eigenes Datenmodell mit Kategorien, Verschlagwortung,
Filtern und Volltextsuche. Franziska führt die Bestände heute in Excel mit fester Struktur —
das ist die gute Nachricht, ein Import ist realistisch. **Vor der Schätzung muss ich diese
Tabellen sehen.**

### 10. Soziale Medien

🟡 Instagram, Facebook, TikTok stehen im Fuß. **YouTube fehlt.** Discord wurde in der
Besprechung selbst infrage gestellt (Ausweisverifizierung widerspricht dem
Anonymitätsanspruch, Bedienbarkeit für weniger technikaffine Menschen). **Empfehlung: erst
mal nicht einbinden**, bis der Verein die Plattformfrage geklärt hat.

### 11. Wall of Shame

❌ Technisch ist das eine Blog-Kategorie mit besonderem Layout — **eine halbe Stunde Arbeit.**
Das Risiko ist juristisch, nicht technisch: Zitate aus Urteilen und Gutachten
öffentlich zu stellen berührt Persönlichkeitsrecht, Urheberrecht an Gutachten und
unter Umständen § 353d StGB (Verbot der Mitteilung über Gerichtsverhandlungen).
**Das gehört vor der ersten Zeile Code zu den Anwälten des Vereins.** Der Verein hat
diesen Rückkanal ausdrücklich angeboten — hier ist er Pflicht, nicht Kür.

### 12. Publikationen

❌ Umfragen, Petitionen, Broschüren, Print. Inhaltlich neu, technisch aber nur eine
Seite mit `download_list` — **klein**, sobald es Inhalte gibt.

### 13. Merchandise

❌ Hoodies, Shirts, Becher, Taschen. Der Verein schrieb: „spätere Shopanbindung sollte
möglich sein." **Empfehlung: keinen eigenen Shop bauen.** Ein eigener Shop bedeutet
Zahlungsabwicklung, Widerrufsrecht, Versand, Retouren, Steuer — für einen Verein mit
knappen Ressourcen ein Fass ohne Boden. Print-on-Demand (Spreadshirt, Shirtigo) mit
einem Link von der Website löst dasselbe Problem ohne laufende Pflicht.
Wenn doch: eigenes Projekt, eigenes Budget.

### 14. Blog / Vereinsnews

✅ Erledigt, siehe Abschnitt 1.

### 15. FAQ

🟡 In der Besprechung **zurückgestellt**. Der `accordion`-Baustein steht bereit, wenn es
so weit ist.

### 16. Impressum und Datenschutz

🟡 Seiten stehen, Inhalte sind aus dem Bestand übernommen. **Die Datenschutzerklärung ist
falsch** — sie beschreibt OneTap, hu-manity und Google Fonts, von denen wir nichts einsetzen.
Muss neu geschrieben und anwaltlich geprüft werden, sobald alle Tools feststehen. Steht
bereits als B1 auf der Übergabe-Checkliste; die Besprechung hat es bestätigt.

---

## 3. Wünsche aus der Besprechung, die nicht im Strukturpapier stehen

| Wunsch | Stand | Einordnung |
|---|---|---|
| **Trigger-Warnung beim Betreten**, schließbar, mit „nicht mehr anzeigen" | ❌ | **Wichtigster fehlender Einzelpunkt.** Es gibt bisher nur den `inhalts_hinweis` als Seitenbaustein — der warnt *innerhalb* einer Seite, nicht am Eingang. Siehe Abschnitt 4 |
| **Glossar / Abkürzungsverzeichnis** (OEG, SGB XIV, GdB, Pflegegrad, Persönliches Budget) | ❌ | Eigenes kleines Modell + Seite. Optional: Begriffe im Fließtext automatisch verlinken. **Hoher Nutzen für die Zielgruppe, kleiner Aufwand** |
| **KI-Suche / Chatbot auf eigener Datenbasis** | ❌ | Siehe Abschnitt 5 — der Punkt, bei dem ich am stärksten zur Vorsicht rate |
| **Kooperationslogos auf Startseite/Footer** | ❌ | siehe 1.9 |
| **Video-Archiv vergangener Veranstaltungen, selbst gehostet** (Teams-Aufzeichnungen) | ❌ | Siehe Abschnitt 5. Ein einstündiges Webinar sind schnell 1–2 GB — Speicher und Bandbreite auf einem VPS sind real |
| **Sunflower-Projekt + Google Maps** | ❌ | Google Maps lädt ungefragt von Google-Servern und überträgt IP-Adressen — passt nicht zum Datenschutzniveau dieses Projekts. **Alternative: OpenStreetMap self-hosted oder eine statische Karte.** Frühestens nach dem Go-Live |
| **Gebärdensprache** | 🟡 | Laut Protokoll über Videos gelöst, nicht über die Seitenstruktur. Der `embed`-Baustein (2-Klick) kann das schon — es fehlen nur die Videos |
| **E-Mail-Bündelung in ein Postfach** | — | Kein Website-Thema. Gehört in die Betriebsberatung, nicht in AN-268 |

---

## 4. Was ich vorschlage — vier Pakete

Sortiert nach Nutzen für die Zielgruppe pro Aufwand.

### Paket 1 — Klein, sofort, hoher Nutzen — **umgesetzt am 05.08.2026**

Das meiste davon ist Ergänzung, kein Neubau. Was davon jetzt im Code steht, steht
in `docs/Komponenten.md`, Abschnitt 15. Punkt 2 („Vergangene Veranstaltungen") entfiel —
er war bereits gebaut.

1. **Trigger-Warnung am Eingang.** Overlay vor dem ersten Seitenaufruf, mit drei Wegen:
   schließen (kommt beim nächsten Besuch wieder), „nicht mehr anzeigen" (`localStorage`),
   und einem direkten Notausgang aus dem Overlay heraus.
   Muss ohne JavaScript unschädlich sein — also serverseitig als normaler Inhalt am
   Seitenanfang, per JS zum Overlay hochgestuft. Umgekehrt ginge es schief: ein JS-Overlay,
   das nicht lädt, gibt den Inhalt ungefiltert frei.
   Text und Umfang gehören dem Verein.
2. **Vergangene Veranstaltungen.** Ansicht auf vorhandene Daten, plus Filter.
3. **Glossar.** Eigene Seite, Begriffe im Panel pflegbar, alphabetisch, sprungfähig.
4. **`download_list` um externe Links erweitern.** Setzt die Entscheidung „Formulare
   verlinken statt hosten" technisch um.
5. **Kooperations-/Förderer-Baustein** (`partner_logos`). Deckt 1.9, 1.10, 1.11 und 7.1
   auf einmal ab — vier Wunschpunkte, ein Baustein.
6. **EPC-QR-Code** für die Bankverbindung, lokal erzeugt.
7. **Fehlende Datensätze:** Selbsthilfegruppe „Killing me Softly", AG 7 „Traumabegleiter",
   YouTube im Fuß.
8. **Seiten anlegen:** Schutzkonzept, Beschwerdemanagement, Publikationen, Projekte —
   als leere, strukturierte Seiten, die der Verein selbst füllt.

### Paket 2 — Mittel, klarer Mehrwert (rund 5–8 Tage)

9. **Wissensdatenbank „Bibliothek".** Eigenes Modell (Urteile, Gutachten, Gesetze,
   Infopool, Folgestörungen), Kategorien, Schlagworte, Filter, Volltextsuche über den
   MySQL-Index. Import aus Franziskas Excel-Tabellen.
   **Voraussetzung: Ich muss die Tabellen vorher sehen.** Ohne das ist jede Zahl geraten.
10. **Seitenweite Suche.** Es gibt heute nur die Blogsuche. Eine Suche über Seiten,
    Beiträge, Veranstaltungen und die Bibliothek ist die ehrliche, sofort nutzbare
    Antwort auf den Wunsch „Suchfunktion für Artikel" — und die Vorstufe zu jeder
    KI-Suche.
11. **Projekte** als eigener Bereich (laufend / abgeschlossen).
12. **Spenden-Sichtbarkeit** entscheiden und umsetzen: mobile Leiste, Startseite,
    und das Popup ab X Seitenaufrufen (Zähler in `localStorage`, kein Tracking,
    kein Cookie, einmal weggeklickt = für längere Zeit weg).

### Paket 3 — Groß, eigenes Angebot (rund 10–15 Tage)

13. **Anmeldefunktion für Gruppen und Veranstaltungen.** Das ist kein Formular, sondern
    ein Verfahren: Anmeldung, Zustimmung zu Teilnahmevereinbarung und Gruppenregeln
    (nachweisbar dokumentiert), Bestätigung, Warteliste, Absage, Teilnehmerliste im Panel,
    Löschfristen. Und: **Wer sich zu „Trauma, Bindung und Beziehung" anmeldet, gibt damit
    eine Gesundheitsinformation preis** — das sind Art.-9-Daten, verschlüsselt zu speichern
    und mit definierter Löschfrist.
14. **Mitgliederbereich** (Login mit Mitgliedsnummer). Das Schema ist vorbereitet, die
    Fläche nicht: Login, Passwort-Vergabe, geschützte Bereiche, Rollen, interner Kalender,
    24/7-WG. Der Verein sagt selbst, der Vorteil einer Mitgliedschaft müsse „noch geschaffen
    werden" — **das ist zuerst eine inhaltliche, dann eine technische Frage.**

### Paket 4 — Erst reden, dann bauen

15. **Wall of Shame** — juristische Freigabe zwingend vor dem Bau.
16. **KI-Suche / Chatbot** — siehe Abschnitt 5.
17. **Video-Archiv** — erst Speicher- und Kostenfrage klären.
18. **Sunflower + Karte** — nach dem Go-Live.
19. **Merchandise / Shop** — Empfehlung Print-on-Demand mit Link.

---

## 5. Wo ich widerspreche

Drei Punkte, bei denen ich nicht einfach abnicken würde:

**KI-Suche mit Vektordatenbank und Chatbot.**
Der Gedanke ist gut und die Begründung („nur auf eigenen Daten, keine Halluzinationen")
zeigt, dass das Problem verstanden ist. Trotzdem:
- „Nur eigene Daten" verhindert Halluzinationen **nicht**. RAG reduziert sie, es beseitigt
  sie nicht. Bei einer Zielgruppe, die nach Fristen im Entschädigungsrecht sucht, ist eine
  falsche Auskunft mit selbstsicherem Ton schlimmer als gar keine Auskunft.
- Jedes Sprachmodell ist ein weiterer Auftragsverarbeiter. Was Menschen in ein Suchfeld
  auf *dieser* Website tippen, sind faktisch Gesundheitsdaten. Die zu einem
  US-Anbieter zu schicken, ist ein anderes Datenschutzniveau als alles andere in diesem
  Projekt — das gesamte übrige Konzept ist darauf gebaut, externe Dienste zu vermeiden.
- Ein self-hosted Modell löst das Datenschutzproblem, kostet aber laufend Geld und Pflege.

**Mein Vorschlag:** erst die ordentliche Volltextsuche (Paket 2, Nr. 10). Die deckt
90 % des Bedarfs, kostet nichts laufend und ist die technische Grundlage, auf der eine
KI-Suche später aufsetzen würde. Dann in Ruhe entscheiden — mit einem Anbieter mit AVV
und EU-Verarbeitung, und mit einem sichtbaren Hinweis, dass die Antwort maschinell ist.

**Selbst gehostete Veranstaltungsvideos.**
Der Wunsch ist nachvollziehbar (keine externe Verbreitung). Aber Video-Hosting auf einem
VPS heißt: Speicherplatz, Bandbreite bei parallelen Zugriffen, und Barrierefreiheit —
**Videos ohne Untertitel sind ein WCAG-Verstoß**, und der Anspruch dieses Projekts ist
AA. Untertitel für ein einstündiges Webinar sind Arbeit, jedes Mal.
Realistische Alternative: Videos nur für Angemeldete, in niedrigerer Auflösung, mit
Untertiteln — und bewusst nur die, bei denen sich der Aufwand lohnt.

**Wall of Shame.**
Der Impuls ist verständlich und das Anliegen berechtigt. Aber es ist der einzige Punkt
auf der Liste, der den Verein selbst in rechtliche Schwierigkeiten bringen kann. Technisch
mache ich das gern — nach schriftlicher anwaltlicher Freigabe, und mit einem klaren
Verfahren, was zitiert wird und was nicht.

---

## 6. Kaufmännisch

AN-268 umfasst 9.726,55 € pauschal. Bereits **nicht** darin enthalten und offen
(steht als C1 auf der Übergabe-Checkliste, mit Tatjana noch nicht geklärt):

- Mehrsprachigkeit (gebaut, in keiner Position)
- Content-Migration von ~20 Seiten und ~120 PDFs
- Datenbereinigung der Alt-Datenbank

Aus diesen beiden Dokumenten kommt dazu:

| Neu | Einordnung |
|---|---|
| Bibliothek / Wissensdatenbank | eigene Position |
| Anmeldeverfahren Gruppen & Veranstaltungen | eigene Position |
| Mitgliederbereich mit Login | eigenes Projekt |
| KI-Suche | eigenes Projekt, laufende Kosten |
| Video-Archiv | eigenes Projekt, laufende Kosten |
| Shop / Merchandise | eigenes Projekt |
| Paket 1 (die kleinen Punkte) | vertretbar im Rahmen, wenn der Rest sauber bepreist wird |

**Empfehlung:** Paket 1 als Geste mitnehmen und dabei sichtbar machen, dass es eine ist.
Paket 2 und 3 als Folgeangebote — der Projektplan hält ohnehin fest, dass „viele
Folgeaufträge erwartet" werden und das Projekt phasenweise läuft. Das ist der Moment,
das ernst zu nehmen, statt die Liste stillschweigend abzuarbeiten.

---

## 7. Rückfragen an den Verein

Vor Paket 1 zu klären:

1. **Text der Trigger-Warnung.** Wortlaut, Umfang, und: auf *jeder* Seite oder nur beim
   ersten Besuch der Website?
2. **Spenden mobil.** Soll Spenden dauerhaft in die mobile Leiste? Dann fällt einer der
   drei bisherigen Einträge weg (Start, Gruppen, Anfrage) — welcher?
3. **Beschwerdemanagement:** eigener Kontaktweg, getrennt vom normalen Anfragen-Postfach?
4. **Partnerliste** mit Logos und Links (Aktion Mensch, Paritätischer, ANUAS, Stiftungen) —
   steht bereits als Aktion im Protokoll.
5. **Welche der 121 Dokumente** gehören zu „Mitglied werden", „Anträge und Formulare",
   „Publikationen"?
6. **Bildformate** (Frage des Vereins): Querformat 3:2 oder 16:9 für Aufmacher und Karten,
   Hochformat 3:4 für Personen, mindestens 2000 px lange Kante, JPG oder PNG im Original —
   die Website erzeugt daraus selbst WebP in mehreren Größen. **Wichtiger als das Format:
   für jedes Bild ein Satz, was darauf zu sehen ist** (Alternativtext), und die schriftliche
   Zusage, dass abgebildete Personen einverstanden sind.

Vor Paket 2 zu klären:

7. **Franziskas Excel-Tabellen einsehen** (Gerichtsurteile, Gutachten, Adresslisten,
   Literatur). Ohne die ist die Bibliothek nicht schätzbar.
8. **Nach wie vielen Seitenaufrufen** soll das Spenden-Banner erscheinen? Und wie lange
   ist Ruhe, wenn jemand es wegklickt? — *Umgesetzt mit 5 Aufrufen und 30 Tagen
   (`config/spendenhinweis.php`); beides bleibt eine Rückfrage an den Verein.*

Vor Paket 3/4 zu klären:

9. **Welchen konkreten Vorteil** hat eine Mitgliedschaft im Mitgliederbereich? Ohne
   Antwort darauf baut man einen leeren Raum.
10. **Anwaltliche Freigabe für die Wall of Shame.**
11. **Discord** — bleibt es dabei, angesichts der Ausweisverifizierung?

---

*Grundlage: Strukturpapier des Vereins und Gemini-Protokoll vom 02.08.2026, abgeglichen
gegen den Code-Stand vom 05.08.2026.*
