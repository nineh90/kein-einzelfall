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
- **Muttersprachliches Gegenlesen.** Die englischen Bedientexte in `lang/en/`
  sind noch von niemandem geprüft worden, der die Sprache spricht. Englisch
  steht deshalb auf **nicht sichtbar** und erscheint in keinem Umschalter.
  Erst nach dem Gegenlesen im Panel unter „Sprachen" freischalten.
  Russisch gibt es seit dem 19.09.2026 nicht mehr (Entscheidung Kevin: nie
  gepflegt, niemand konnte es gegenlesen). Wird es doch gewünscht, legt der
  Verein es im Panel an — die Bedientexte (`lang/ru/`) müssten dann neu
  entstehen.

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

### A8 · Unterzeile über den Einstiegskarten der Startseite

Die Überschrift der Startseite lautet seit dem 30.07.2026 „Keiner soll mehr
sagen müssen: „Ich hab es nicht gewusst!"" — wie im freigegebenen Mockup, mit
der handgezeichneten Linie unter dem Zitat. Das Zitat selbst steht seit dem
22.09.2026 (KEV-16) in der Handschrift des Vereins — derselben wie beim Leitsatz
„Opferhilfe für soziale Gerechtigkeit!" unter der Vereinsarbeit.

Genau dieser Satz stand vorher als Unterzeile über den vier Einstiegskarten.
Damit er nicht zweimal auf derselben Seite steht, ist die Unterzeile **vorerst
leer**. Das ist so abgesprochen und sieht nicht falsch aus — die Überschrift
„Unsere Aufgabe" steht weiterhin darüber.

**Frage an den Verein:** Soll dort etwas anderes stehen? Das Feld heisst im
Panel „Unterzeile" und liegt im Baustein „Einstiegskarten" der Startseite.

### A9 · Weitere offene Punkte
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

### A10 · Rückfragen aus Paket 1 (05.08.2026)

Alles hier gehört zu dem, was am 05.08.2026 aus dem Besprechungs-Abgleich gebaut
wurde (`docs/Abgleich-Besprechung-2026-08-02.md`, `docs/Komponenten.md` Abschnitt 17).
Es läuft, aber an diesen Stellen fehlt eine Entscheidung des Vereins.

- [ ] **Wortlaut der Trigger-Warnung.** Der Text unter `/trigger-warnung` ist ein
  Vorschlag von uns und bewusst nüchtern: Er beschreibt, worum es auf der Website
  geht, und sagt nichts im Namen des Vereins. **Er ist veröffentlicht und damit
  auf jeder Seite sichtbar** — bitte gegenlesen und ändern oder bestätigen. Im
  Panel unter „Seiten → Hinweis zu den Inhalten dieser Website".
- [ ] **Wie oft soll die Warnung kommen?** Gebaut ist: einmal pro Besuch. Wer sie
  wegklickt, sieht sie beim nächsten Besuch wieder; wer „nicht mehr anzeigen"
  wählt, nie wieder. Alternative wäre: auf jeder einzelnen Seite erneut. Das
  wäre für Menschen, die viel lesen, schnell zermürbend — deshalb der Vorschlag
  so. Bitte bestätigen.
- [ ] **Glossar: die drei Entwürfe freigeben.** GdB, Pflegegrad und Persönliches
  Budget liegen im Panel als Entwurf und sind auf der Website unsichtbar. Der
  Text beginnt jeweils mit „ENTWURF — bitte prüfen". Wir schreiben dazu bewusst
  keine verbindliche Fassung: Wer das liest, entscheidet danach womöglich über
  eine Frist. **Das ist ein Fall für die Anwälte des Vereins.** Weitere Begriffe
  legt der Verein selbst an — je Eintrag reichen zwei, drei Sätze.
- [ ] **Schreibweise „Killing me Softly".** Im Strukturpapier steht „Killen me
  Softly". Wir sind von einem Tippfehler ausgegangen. Falls nicht: im Panel unter
  „Gruppen" ändern.
- [ ] **Adresse des YouTube-Kanals.** Er wurde in der Besprechung genannt, die
  Adresse nicht. Wir raten sie nicht — eine falsche Adresse führt entweder ins
  Leere oder zu einem fremden Kanal. Sobald sie da ist, ist es eine Zeile in
  `config/navigation.php` (steht dort auskommentiert bereit).
- [ ] **Partnerliste mit Logos.** Aktion Mensch, Der Paritätische, ANUAS und die
  Stiftungen, die gefördert haben. Gebraucht werden: Name, gewünschte Rolle
  („Förderer seit 2025"), Adresse der Website und die Logodatei. Steht bereits
  als Aktion im Protokoll. Der Baustein „Partner und Unterstützer" wartet darauf.
  **Logos bitte als Datei, nicht als Link zum Partner** — ein fremd geladenes
  Bild überträgt die IP-Adresse unserer Besucherinnen dorthin.
- [ ] **Kontoinhaber für den Spenden-QR-Code.** Genau so, wie er bei der Bank
  hinterlegt ist. Er landet im Überweisungsformular der spendenden Person; weicht
  er ab, kommt die Überweisung zurück. Im Panel im Baustein „Spendenmöglichkeiten"
  auf der Startseite (Feld „Kontoinhaber"); leer heisst „KE!N EINZELFALL e.V.".
- [ ] **Spendenhinweis abnehmen.** Der Kasten für wiederkehrende Besucherinnen
  (KEV-6) erscheint ab dem 5. Seitenaufruf und gibt nach dem Wegklicken 30 Tage
  Ruhe — beides unser Vorschlag, die Besprechung nannte „5 bis 10". Der Wortlaut
  („Hilft dir, was du hier findest? …") ist ebenfalls von uns; er ist eine Bitte
  des Vereins und sollte in dessen Worten stehen. Zahlen in
  `config/spendenhinweis.php`, Texte in `lang/*/rahmen.php` unter `spendenhinweis`.
  Der Zähler steht in `config/speicher.php` und gehört damit in den Abschnitt
  „Speicherung im Browser" der Datenschutzerklärung.
- [ ] **PayPal-Spendenlink bestätigen.** Auf der Startseite steht der Link der
  Altseite (`paypal.com/donate?business=paypal@kein-einzelfall.de`). Falls der
  Verein inzwischen einen PayPal.me-Link oder eine Spendenkampagne hat, im
  Panel im Baustein „Spendenmöglichkeiten" ersetzen.
- [ ] **Die vier neuen Seiten füllen:** Schutzkonzept, Beschwerdemanagement,
  Projekte, Publikationen. Sie liegen im Panel als Entwurf bereit, mit Adresse und
  Gliederung, aber ohne Text. Ins Menü nehmen wir sie auf, sobald sie Inhalt
  haben. **Beim Beschwerdemanagement zusätzlich zu klären:** eigener Kontaktweg,
  getrennt vom normalen Anfragen-Postfach? Beschwerden über den Verein sollten
  nicht dort landen, wo sie die Betroffenen selbst lesen.
- [ ] **Antragsformulare: welche Adressen?** Gebaut ist die Umsetzung der
  Entscheidung „verlinken statt hosten". Gebraucht wird jetzt je Formular die
  Adresse bei der Behörde und deren Name (z.B. „Deutsche Rentenversicherung").
- [ ] **Teamseite abnehmen (20.09.2026).** Jetzt alle sieben Personen mit Foto,
  gegliedert wie auf der Altseite (Vorstand · Team · Im Hintergrund). Bitte
  Rollen, Reihenfolge und die Bereichsnamen prüfen — „Team" und „Im Hintergrund"
  sind unsere Wörter, die Altseite hat dort keine Überschriften. Die
  Kontakt-Mailadressen je Person (belmar@, kuenstler@, …) stehen auf der
  Altseite unter jedem Profil; bei uns gibt es dafür noch kein Feld. Gewünscht?
- [ ] **Verdächtige Datei in der WordPress-Mediathek.** Dort liegt seit Juli
  2026 `w2sx8a07e0l.php_.jpg` — kein Bild (keine Maße), ein Dateiname wie aus
  einem Upload-Angriff. Bitte im WordPress-Admin ansehen und löschen; falls
  sie niemand aus dem Verein hochgeladen hat, Passwörter der Altseite wechseln.

### A11 · Neun Seitentitel stammen von uns

Neun Seiten der Altseite haben gar keine Überschrift — WordPress gibt dort kein
`<h1>` aus. Weil ein Titel ohne Alternative aus dem Slug gebaut wird und Slugs
keine Umlaute kennen, stand über der Teamseite bis zum 22.09.2026 wörtlich
„Ueber Uns Vorstand Und Team". Seither tragen sie ausgeschriebene Titel,
wortgleich mit dem jeweiligen Menüpunkt:

| Seite | Titel |
|---|---|
| `/ueber-uns-vorstand-und-team` | Über uns – Vorstand und Team |
| `/das-hilfesystem` | Das Hilfesystem |
| `/fsm-erweitertes-hilfesystem` | FSM – Erweitertes Hilfesystem |
| `/buerokratie-labyrinth` | Das Bürokratie-Labyrinth |
| `/kein-einzelfall-im-dialog` | KE!N EINZELFALL im Dialog |
| `/trauma-bindung-und-beziehung` | Trauma, Bindung und Beziehung |
| `/traumafolgestoerungen-verstehen` | Traumafolgestörungen verstehen |
| `/istanbul-konvention` | Istanbul-Konvention |
| `/unterstuetzung` | Unterstützung |

**Frage an den Verein:** Vertraglich stellt der Verein die Inhalte — diese neun
Titel sind die einzige Ausnahme, weil die Altseite schlicht keine hergibt. Bitte
einmal durchsehen. Ändern lässt sich jeder im Panel im Feld „Titel"; was dort
steht, wird von keinem Update überschrieben.

### A12 · Verweis auf REHADAT (Text von uns)

Auf `/wissen` steht seit dem 22.09.2026 ein Hinweis-Kasten „Urteile zum
Schwerbehindertenrecht" mit Verweis auf REHADAT-Recht. Hintergrund in
`docs/KEV-4-REHADAT.md`: REHADAT ist dort erschöpfend, eine eigene Sammlung
daneben wäre Doppelarbeit — beim Sozialen Entschädigungsrecht dagegen ist
REHADAT dünn, dort entsteht später die eigene Bibliothek.

**Frage an den Verein:** Der Text stammt von uns. Bitte gegenlesen — und sagen,
ob der Verweis so gewollt ist. Er ist im Panel unter `/wissen` im letzten
Baustein änder- oder löschbar.

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
- [ ] **Aufzählungen der Altseite nachtragen.** Der Importer hat bis 20.09.2026
      keine `<li>` gelesen (siehe `docs/Komponenten.md`, Abschnitt 24). Auf neun
      Seiten fehlen deshalb Listen — unter anderem die Notfallnummern auf
      `/anfragen`, Referent/Datum/Ort auf den Veranstaltungsseiten, die
      „Warum spenden"-Punkte, die Betroffenenrechte im Datenschutz. Der frische
      Abzug enthält sie; nachgetragen wird pro Seite von Hand, nicht per
      Neu-Import, weil die Seiten inzwischen bearbeitet sind. Eigenes Ticket.
- [ ] **Impressum und Datenschutz gegenlesen.** Beim Übernehmen aus Elementor sind
      aus 21 Roh-Blöcken 13 geworden (der Rest waren Layout-Reste ohne Text). Beim
      Impressum sind es 8 Blöcke mit zusammen nur 40 Wörtern — sieht nach
      zerstückelten Zeilen aus. Bei beiden Seiten ist Vollständigkeit rechtlich
      relevant.
- [ ] **Datenschutzerklärung neu schreiben.** Die übernommene stammt von der
      Altseite und beschreibt deren Tools (OneTap, hu-manity-Banner, Google Fonts) —
      nichts davon setzen wir ein. Was wir tatsächlich verarbeiten, steht in
      `docs/Komponenten.md` Abschnitt 6 und 7.
      **Für den Abschnitt „Speicherung im Browser“ ist `config/speicher.php` die
      Vorlage** — dort steht jeder Schlüssel, den die Seite ablegt, mit einer
      verständlichen Beschreibung. Die Liste ist bewusst die einzige Quelle;
      wächst sie, wächst der Abschnitt mit. Rechtlich einwilligungsfrei
      (§ 25 Abs. 2 Nr. 2 TDDDG), aber nennen muss man es.

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

### C0 · Neu aus der Besprechung vom 02.08.2026 — vor der nächsten Rechnung klären

Aus dem Strukturpapier und der Besprechung kommen Wünsche, die in **keiner**
Position von AN-268 stehen. Die vollständige Einordnung steht in
`docs/Abgleich-Besprechung-2026-08-02.md`, Abschnitt 6. Kurz:

- **Paket 1 ist am 05.08.2026 gebaut** — Trigger-Warnung, Glossar, externe
  Formularverweise, Partner-Baustein, Spenden-QR-Code, vier neue Bereiche. Das
  ist im Rahmen vertretbar, **wenn** der Rest sauber bepreist wird. Es sollte
  sichtbar als Geste benannt und nicht stillschweigend verbucht werden.
- **Eigene Position nötig:** Bibliothek/Wissensdatenbank (Urteile, Gutachten,
  Gesetze, Infopool), Anmeldeverfahren für Gruppen und Veranstaltungen,
  seitenweite Suche.
- **Eigenes Projekt:** Mitgliederbereich mit Login, KI-Suche, Video-Archiv,
  Merchandise/Shop.
- **Vor der Schätzung der Bibliothek**: Franziskas Excel-Tabellen einsehen. Ohne
  sie ist jede Zahl geraten. Das Datenmodell ist in `docs/KEV-4-REHADAT.md`
  vorbereitet — dort steht auch, warum der Schwerpunkt auf dem Sozialen
  Entschädigungsrecht liegen sollte und nicht auf dem Schwerbehindertenrecht.

### C1 · Drei Positionen fehlen im Angebot
- **Content-Migration.** „Keine Content-Erstellung" heißt nicht „kein Aufwand".
  23 Seiten und über 100 Dokumente einzupflegen ist erhebliche Arbeit und in
  keiner Position bepreist.
- **Nachtragsvorbehalt Datenbereinigung.** Stand im ursprünglichen Projektplan
  und war in der gekürzten Fassung verschwunden. Gegen einen ausdrücklich
  unbekannten („wirren") Altbestand stehen 1.299 € pauschal — das größte
  kaufmännische Risiko im Projekt.
- **Mehrsprachigkeit.** Steht in **keiner** Position von AN-268. Jede weitere
  Sprache bedeutet zusätzlichen Pflegeaufwand, zusätzliche Qualitätssicherung
  und einen erweiterten Barrierefreiheits-Durchlauf — bei anderen Alphabeten
  auch eigene Schriftschnitte (Fraunces kann z.B. kein Kyrillisch). Das technische
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
