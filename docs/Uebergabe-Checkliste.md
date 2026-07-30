# Wichtige Punkte — für Kevin und Tatjana

Stand 27.07.2026 (nach der Mehrsprachigkeits-Session). Alles, was vor dem Go-Live entschieden, geprüft oder besorgt
werden muss. Sortiert nach Dringlichkeit.

---

## A. Fragen an den Verein (Tatjana)

### A1 · Notfallnummern gegenprüfen — vor Go-Live
In `config/hilfe.php` stehen fünf Nummern, die auf der Startseite und den
Trauma-Seiten prominent erscheinen:

| Dienst | Nummer |
|---|---|
| Opfer-Telefon WEISSER RING | 116 006 |
| Telefonseelsorge | 0800 111 0 111 |
| Hilfetelefon Gewalt gegen Frauen | 116 016 |
| Hilfetelefon Sexueller Missbrauch | 0800 22 55 530 |
| Nummer gegen Kummer | 116 111 |

**Bitte auf Richtigkeit und Zeiten prüfen.** Der Verein kennt die Landschaft
besser als wir. Eine falsche Nummer wäre hier ein echter Schaden.
Fehlt ein Dienst, den ihr regelmäßig empfehlt?

### A2 · Aufbewahrungsfristen für Anfragen — juristisch klären
Vorschlag steht in `config/anfragen.php`: erledigte Anfragen 90 Tage nach
Abschluss, unbearbeitete 365 Tage nach Eingang.

**Das ist unser Vorschlag, kein Rechtsrat.** Der Verein stellt eigene Anwälte
zur Verfügung — der richtige Weg. Besonders zu klären:
**Müssen Anfragen mit Bezug zu laufenden Verfahren länger aufbewahrt werden?**
Falls ja, braucht es dafür ein eigenes Kennzeichen statt einer pauschal
längeren Frist für alle.

### A3 · Kennzahlen bestätigen oder streichen
Das Design-Mockup zeigte „1.000+ erreichte Menschen". Diese Zahl steht **nirgends**
auf der bestehenden Website — sie ist derzeit **nicht** eingebaut. Falls solche
Zahlen gewünscht sind, müssen sie belegbar sein.

### A4 · Die 79 unveröffentlichten Dokumente
Der Medienbestand enthält **121 Dokumente**, aber nur **31** sind auf der Website
verlinkt. Die übrigen 79 sind vollständige Infoblatt-Reihen:

| Reihe | Anz. | Thema |
|---|---|---|
| `6.5.1.x` | 27 | Atteste, Alltagsbeeinträchtigungen |
| `6.5.5.x` | 25 | Grad der Behinderung |
| `6.5.7.x` | 5 | Kfz-Hilfe |
| weitere | 22 | Jugendamt, Behörden-Schriftwechsel, Formulare |

**Der Verein hat also deutlich mehr erarbeitet, als die Website zeigt.** Für den
neuen Wissensbereich ist das der größte inhaltliche Hebel im Projekt.

Zu klären: Welche Reihen sollen öffentlich sein? Manches sieht nach internen
Formularen aus („Verschwiegenheitserklärung", „Einverständnis Daten/Akten
speichern").

> **Teilweise beantwortet durch A7:** Tatjana hat genau diese beiden in der
> VVT-Liste rot markiert (1.5.5 und 1.5.6). Sie sind also **Musterdokumente
> zum Ausfüllen**, keine ausgefüllten Akten — und gehören damit öffentlich.
> Das entschärft die Frage für diese Gruppe; die Warnung unten gilt für die
> Behörden-Schriftwechsel unverändert.

> ⚠️ **Sicherheitshinweis:** Diese Dateien sind **heute schon öffentlich abrufbar**,
> nur nicht verlinkt. Ich habe sie ohne jede Zugangsdaten herunterladen können.
> Bei den Behörden-Schriftwechseln und Stellungnahmen muss geprüft werden, ob
> darin personenbezogene Daten stehen. Nicht-Verlinken ist kein Zugriffsschutz.

**Stand:** Die **31 verlinkten Dokumente sind übernommen** und liegen unter
`public/dokumente/`; alle 32 Verweise auf der neuen Seite funktionieren, die
alten `/wp-content/`-Adressen leiten dauerhaft weiter. Die **übrigen 90 sind
bewusst nicht geholt** — genau wegen der Frage oben. Sobald der Verein je Reihe
freigibt: `php artisan dokumente:holen --alle`.

**Wichtiger Befund vom 30.07.2026 — die rote Markierung hilft hier nicht weiter.**

Die Dateinamen des Medienbestands tragen die VVT-Nummer (116 von 121). Damit
liess sich die rote Markierung direkt auf echte Dateien abbilden. Ergebnis:

| | |
|---|---|
| Dateien unter rot markierten Ästen | **7** — und die sind längst geholt |
| Dateien unter *nicht* markierten Ästen | **90** |

Die 90 unverlinkten Dateien liegen fast vollständig in **Abschnitt 6**, und
**Abschnitt 6 ist an keiner Stelle rot markiert**:

| Ast | Anz. | Bezeichnung |
|---|---|---|
| 6.5 | **62** | Infoblätter (Atteste, Grad der Behinderung, Kfz-Hilfe) |
| 6.2 | 14 | Kontakt mit Bundesministerien |
| 6.1 | 8 | Kontakt mit uns |
| sonstige | 6 | ohne VVT-Nummer |

Zwei Dinge folgen daraus:

1. **Die rot markierten Dokumente existieren gröstenteils noch nicht als Datei.**
   Dokumentenvollmacht, Schweigepflichtsentbindung, Vollmachtserklärung und die
   übrigen Musterformulare sind im Medienbestand nicht auffindbar. Sie muss der
   Verein liefern.
2. **Die 62 Infoblätter sind weiterhin unentschieden.** Genau die nennt A4 „den
   grössten inhaltlichen Hebel im Projekt". Sie sind vorhanden, geschwärzt und
   freigegeben — sie stehen nur auf keiner Liste, die sagt: bitte veröffentlichen.

**Die eine Frage, die das auflöst:** Sollen die 62 Infoblätter aus 6.5 auf die
Website? Wenn ja, ist es ein Befehl und ein Nachmittag Zuordnungsarbeit.

Nebenbei aufgefallen: Dieselben Musterformulare stehen in der VVT **zweimal** —
rot unter 1.5.3–1.5.5 / 1.5.21 / 1.5.22 (Verwaltung) und ein zweites Mal
unmarkiert unter **6.1.3 „Formulare"** als Teil von „Kontakt mit uns". Inhaltlich
dasselbe, nur anders einsortiert. Für die Website ist die Nummer egal — gemeint
sind dieselben Dokumente.

> ✅ **Geklärt am 30.07.2026 (Kevin):** Die Dokumente sind **alle geschwärzt
> und dürfen von uns genutzt werden.** Damit ist der Datenschutz-Vorbehalt für
> den gesamten Medienbestand erledigt — auch für die sieben
> Behörden-Schriftwechsel auf `/fsm-erweitertes-hilfesystem`.
>
> **Was damit *nicht* beantwortet ist:** welche der 90 bisher unverlinkten
> Dokumente **veröffentlicht werden sollen**. Das ist keine Datenschutz-, sondern
> eine redaktionelle Frage — siehe den Kasten unten.

### A5 · Texte für Leichte Sprache
**Die technische Frage ist entschieden:** Leichte Sprache ist jetzt eine eigene
**Fassung** einer Seite, keine eigene Sprache. Sie bekommt eine eigene Adresse
unter `/leichte-sprache/…`, wird von der Hauptfassung aus sichtbar verlinkt und
ist damit verlinkbar, als Lesezeichen speicherbar und auffindbar (BITV 2.0 § 4).
Sie bleibt `lang="de"` und bekommt kein `hreflang` — sie *ist* Deutsch. Im Panel
wählt man beim Anlegen einer Seite die Fassung. Der alte Baustein-Typ
`leichte_sprache` bleibt für kurze Zusammenfassungen innerhalb einer schweren
Seite bestehen; beides ist üblich und nebeneinander sinnvoll.

Was bleibt: **die Texte fehlen.** Leichte Sprache ist eine eigene Disziplin mit
Regelwerk und wird idealerweise von einer Prüfgruppe aus der Zielgruppe
abgenommen.

Wir können Entwürfe liefern — aber bei den Rechtsthemen (OEG/SGB XIV, GdB,
Erwerbsminderung, Widerspruchsfristen) **muss der Verein gegenlesen**.
„Vereinfacht" und „falsch" liegen dort dicht beieinander, und die Leute treffen
danach Entscheidungen über Anträge und Fristen.

### A6 · Mehrsprachigkeit — Inhalte und Gegenlesen

Struktur, Adressen, Umschalter und Schriften stehen. **Übersetzt ist nur die
Bedienoberfläche** — Knöpfe, Vorlesehilfen, Wegweiser. Alles Inhaltliche fehlt
noch, und zwar bewusst:

- **Die Inhalte liefert der Verein.** Es geht um Opferrechte, Fristen und
  Notfallnummern. Eine maschinelle Übersetzung kann hier realen Schaden
  anrichten, deshalb ist keine gemacht worden. Auch die Menüpunkte sind zum
  grossen Teil Seitentitel und damit Fachbegriffe des Sozialrechts
  („Erwerbsminderungsrente", „FSM – Erweitertes Hilfesystem") — sie erscheinen
  automatisch in der Fremdsprache, sobald die zugehörige Seite übersetzt ist.
- **Welche Seiten zuerst?** Alle 24 zu übersetzen ist viel. Vorschlag für eine
  erste Runde: Startseite, Anfragen & Austausch, Selbsthilfegruppen, Kontakt,
  Das Hilfesystem. Der Rest fällt sichtbar auf Deutsch zurück, mit Hinweis in
  der jeweiligen Sprache — das ist ein regulärer Zustand, kein Fehler.
- **Notfallnummern je Sprache** — siehe A1. Dieselbe Frage, jetzt dringender:
  Was zeigen wir russisch- oder englischsprachigen Besuchern? Gibt es
  mehrsprachige Hotlines? Das „Hilfetelefon Gewalt gegen Frauen" (116 016)
  nennt selbst 18 Sprachen; das gehört geprüft und, wo zutreffend, in die
  jeweilige Sprachfassung übernommen.
- **Muttersprachliches Gegenlesen.** Die englischen und russischen
  Bedientexte in `lang/en/` und `lang/ru/` sind noch von niemandem geprüft
  worden, der die Sprache spricht. Beide Sprachen stehen deshalb auf
  **nicht sichtbar** und erscheinen in keinem Umschalter. Erst nach dem
  Gegenlesen im Panel unter „Sprachen" freischalten.

### A7 · VVT-Liste — vier Rückfragen vor dem Einpflegen

Tatjana hat in der VVT-Liste (Stand 29.07.26) 32 Äste rot markiert, zusammen
127 Positionen. Die vollständige Zuordnung zu Zielseiten steht in
[`docs/VVT-Zuordnung.md`](VVT-Zuordnung.md). Überwiegend sind es
Musterdokumente zum Herunterladen — vier Äste passen aber nicht in dieses
Muster und brauchen eine Antwort, **bevor** etwas veröffentlicht wird:

1. **5.4 Adressdatenbank** — öffentliches Verzeichnis von Anlaufstellen oder
   interne Kontaktliste? Bei Letzterem wäre eine Veröffentlichung ein
   meldepflichtiger Datenschutzvorfall. **Höchste Priorität.**
2. **1.10.7–1.10.11** (E-Mail/Logo/Name, M365-Lizenz, IT-Nutzungsregeln) —
   Formulare für Mitglieder und Ehrenamt, nicht für Betroffene. Öffentlich
   oder in den Mitgliederbereich, den es noch nicht gibt?
3. **1.5.9 Dienstleister Daten/Akten** und **1.10.5 DSGVO** — Musterformular
   oder internes Verzeichnis?
4. **1.5.7 FZ-Befreiung** — steht FZ für Führungszeugnis?

Ausserdem: Die Äste 4.1, 4.2, 4.3, 4.5 und 5.2.2 sind keine Dokumente, sondern
Inhaltsbereiche, für die es bereits Seiten gibt. Dort geht es um einen
Abgleich — was in der Liste steht und auf der Seite fehlt (z.B. Assistenzhund,
Fallmanager, Helfernetzwerk, Opferanwalt, Persönliches Budget, §109 SGG,
KFZ-Hilfe), muss der Verein liefern.

### A8 · Weitere offene Punkte
- **Bestehende Datenbank:** Wir haben weiterhin keinen Zugriff. Vor der
  Aufwandsschätzung für die Bereinigung unbedingt Einsicht nehmen.
- **Google Search Console:** Zugang sichern, **bevor** am Altsystem etwas geändert
  wird. Ohne Ausgangswerte lässt sich nach dem Launch nicht belegen, dass sich
  SEO nicht verschlechtert hat — und genau das war die Zusage.
- **Logo:** liegt nur als schwarzes PNG vor. Für die neue Seite braucht es eine
  Vektorfassung und eine helle Variante für dunkle Flächen.
- **Bildmaterial:** Die Seite arbeitet derzeit mit Platzhaltern.
- **Inhaltshinweise:** Auf welchen Seiten sollen sie stehen? Vorschlag:
  Trauma/Bindung, Traumafolgestörungen, FSM.

---

## B. Technisch vor dem Go-Live (Kevin)

### B1 · Muss
- [ ] **`APP_KEY` sichern.** Die Verschlüsselung der Anfragen hängt daran. Geht der
      Schlüssel verloren, sind alle Anfragen unwiederbringlich weg.
- [ ] **`SESSION_SECURE_COOKIE=true`** setzen. Steht derzeit auf `NULL`, damit ginge
      das Session-Cookie auch über HTTP raus.
- [ ] **Admin-Konto von Hand anlegen.** Lokal legt `bin/start` per `AdminSeeder`
      `admin@kein-einzelfall.test` / `kein-einzelfall` an. Der Seeder überspringt
      sich in Produktion selbst — dort also `php artisan make:filament-user` und
      anschliessend `panel_zugang` setzen (siehe README).
- [ ] **Beispieldaten löschen:** aktuell zwei Termine. Blogbeiträge und Anfragen
      sind derzeit keine im Bestand — vor dem Go-Live gegenprüfen.
- [ ] **`/module-demo` entfernen** (interne Vorschau).
- [ ] **`MAIL_ANFRAGEN_AN`** setzen, sonst kommt keine Benachrichtigung an.
- [ ] **Scheduler einrichten** (`* * * * * php artisan schedule:run`), sonst laufen
      die Löschfristen nie.
- [ ] **Impressum und Datenschutz gegenlesen.** Beim Übernehmen aus Elementor sind
      aus 21 Roh-Blöcken 13 geworden (der Rest waren Layout-Reste ohne Text). Beim
      Impressum sind es 8 Blöcke mit zusammen nur 40 Wörtern — sieht nach
      zerstückelten Zeilen aus. Bei beiden Seiten ist Vollständigkeit rechtlich
      relevant.
- [ ] **Datenschutzerklärung neu schreiben.** Die übernommene stammt von der
      Altseite und beschreibt deren Tools (OneTap, hu-manity-Banner, Google Fonts) —
      nichts davon setzen wir ein. Was wir tatsächlich verarbeiten, steht in
      `docs/Komponenten.md` Abschnitt 6 und 7.

### B2 · Sollte
- [ ] Hosting entscheiden (VPS Deutschland) + **AVV mit dem Hoster**
- [ ] AVV zwischen Nils-Digital und Verein, sobald wir echte Daten sehen
- [ ] 301-Weiterleitungen nach dem Launch prüfen: `redirects.treffer` im Panel
      zeigt, welche Regel greift. Eine Regel mit 0 Aufrufen nach Wochen ist
      überflüssig oder falsch geschrieben.
- [ ] Neue `sitemap.xml` bei der Search Console einreichen
- [ ] Google-Business-Eintrag aktualisieren
- [ ] 2–4 Wochen engmaschig auf Crawling-Fehler schauen
- [ ] **Alte Seite erst abschalten, wenn die Weiterleitungen live und geprüft sind**

---

## C. Kaufmännisch (Kevin)

### C1 · Drei Positionen fehlen im Angebot
- **Content-Migration.** „Keine Content-Erstellung" heißt nicht „kein Aufwand".
  23 Seiten und über 100 Dokumente einzupflegen ist erhebliche Arbeit und in
  keiner Position bepreist.
- **Nachtragsvorbehalt Datenbereinigung.** Stand im ursprünglichen Projektplan
  und war in der gekürzten Fassung verschwunden. Gegen einen ausdrücklich
  unbekannten („wirren") Altbestand stehen 1.299 € pauschal — das größte
  kaufmännische Risiko im Projekt.
- **Mehrsprachigkeit.** Steht in **keiner** Position von AN-268. Drei Sprachen
  bedeuten dreifachen Pflegeaufwand, dreifache Qualitätssicherung, eine
  zusätzliche Displayschrift (Fraunces kann kein Kyrillisch) und einen
  erweiterten Barrierefreiheits-Durchlauf je Sprache. Das technische
  Grundgerüst steht; der laufende Aufwand beginnt erst danach. **Vor der
  Abnahme mit Tatjana klären.**

### C2 · Barrierefreiheit braucht einen Maßstab
„Maximal" und „gleichwertig oder besser" sind nicht abnahmefähig. Vorschlag:
**WCAG 2.1 AA vertraglich zusichern**, AAA als Ziel ohne Zusage. Dazu ein
Prüfverfahren vereinbaren. **Der messbare Teil steht inzwischen:**
`npm run test:a11y` prüft mit axe-core neun Seiten je Sprache, die vier
Zustände der Darstellungs-Einstellungen und den Reflow bei 320 px — 36
Durchläufe, aktuell ohne Befund. Der erste Lauf fand neun echte Verstösse,
darunter einen Fussbereich, den ausgerechnet der Modus „hoher Kontrast"
unlesbar machte.

Das ersetzt die Abnahme nicht: axe-core findet 30–50 % der Verstösse. Es fehlen
weiterhin der manuelle Screenreader-Test, der Tastatur-Durchlauf und eine CI,
die den Lauf bei jedem Commit auslöst. Sonst wird „ist das barrierefrei genug?"
eine endlose Abnahme-Diskussion.

### C3 · Budget-Einschätzung unverändert
9.726 € entsprechen grob 130–150 Stunden. Der Umfang liegt eher bei 35–50 Tagen.

---

## D. Was der Verein sofort verbessert bekommt

Argumente fürs Kundengespräch — alles gemessen, nicht behauptet:

| | Altseite | Neu |
|---|---|---|
| Navigation | nur per JavaScript erzeugt, für Google unsichtbar | im Server-HTML |
| Notausgang | nur mit JavaScript, kein Tastaturkürzel | echter Link, funktioniert ohne JS, 3× ESC |
| Google Fonts | lädt ungefragt von Google-Servern | lokal, kein externer Aufruf |
| Cookie-Banner | vorhanden, aber `blocking:false` — blockiert nichts | **nicht nötig**, weil nichts zu blockieren ist |
| betterplace-Widgets | laden ungefragt | erst nach ausdrücklicher Zustimmung |
| Sicherheits-Header | **0 von 5** | alle, inkl. CSP mit Nonce |
| Startseite | 436 KB HTML für 479 Wörter | ~50 KB |
| Kontaktformular | Name und E-Mail Pflicht, unverschlüsselt | freiwillig, verschlüsselt, anonym möglich |
| Strukturierte Daten | Organization, WebPage | zusätzlich Article und Event |
| Tote Links | `/selbsthilfegruppen-2`, `/kontaktformular` | repariert und weitergeleitet |

**Ein Banner, der nichts verhindert, ist schlechter als keiner** — er suggeriert
Kontrolle, die es nicht gibt. Dass die neue Seite ohne auskommt, ist ein Ergebnis
sauberer Technik, keine Nachlässigkeit.

---

## E. Kleinigkeiten aus dem Bestand

- Tippfehler auf `/mitgliedschaft`: „Hlfe zum Ausfüllen"
- Eine `.docx` im Medienbestand (`6.5.4.4.-KE-Stellungnahme-BBM.docx`).
  Word-Dateien gehören nicht ins Web — sie transportieren Metadaten und oft
  Änderungsverfolgung. Als PDF exportieren oder entfernen.
- 10 Dubletten im Medienbestand (WordPress-`-1`-Varianten derselben Datei)
