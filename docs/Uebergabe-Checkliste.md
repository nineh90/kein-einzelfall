# Wichtige Punkte — für Kevin und Tatjana

Stand 27.07.2026 (nach der Mehrsprachigkeits-Session). Alles, was vor dem Go-Live entschieden, geprüft oder besorgt
werden muss. Sortiert nach Dringlichkeit.

---

## A. Fragen an den Verein (Tatjana)

### A1 · Notfallnummern gegenprüfen — erledigt
*Vom Verein bestätigt am 30.10.2026 (Taddi: „stimmt alles“), keine Nummer fehlt.*

In `config/hilfe.php` stehen sechs Nummern. Sie erscheinen auf der Startseite,
in der Fusszeile, auf der Fehlerseite und seit 08.10.2026 auch auf `/anfragen`
(dort stand vorher eine eigene Liste der Altseite):

| Dienst | Nummer | Zeiten |
|---|---|---|
| Opfer-Telefon WEISSER RING | 116 006 | täglich 7–22 Uhr |
| TelefonSeelsorge | 116 123 | rund um die Uhr |
| Hilfetelefon Gewalt gegen Frauen | 116 016 | rund um die Uhr |
| Hilfetelefon Gewalt an Männern | 0800 123 99 00 | Mo–Do 8–20, Fr 8–15 Uhr |
| Hilfetelefon Sexueller Missbrauch | 0800 22 55 530 | Mo, Mi, Fr 9–14, Di, Do 15–20 Uhr |
| Nummer gegen Kummer | 116 111 | Mo–Sa 14–20 Uhr |

**Bitte auf Richtigkeit und Zeiten prüfen.** Der Verein kennt die Landschaft
besser als wir. Eine falsche Nummer wäre hier ein echter Schaden.
Fehlt ein Dienst, den ihr regelmäßig empfehlt? Die Altseite nannte auf
`/anfragen` zusätzlich den **Krisendienst 0800 / 655 3000**. Das ist der
Krisendienst **Bayern**, regional, deshalb steht er nicht mehr dabei. Soll er
wieder rein, dann mit „(Bayern)“.

### A2 · Aufbewahrungsfristen für Anfragen — bestätigt
*Vom Verein bestätigt am 30.10.2026 (Taddi: „das passt“), auch die Fristen der
Gruppenanmeldungen (30/90 Tage). Der Verein hat keine Anwälte; eine juristische
Prüfung gibt es also nicht.*

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
| `6.5.1.x` | 27 | Atteste, Alltagsbeeinträchtigungen (seit KEV-144 auf /atteste) |
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

Seit KEV-144 sind die **27 Attestvorlagen (6.5.1)** auf der neuen Seite
/atteste verlinkt und liegen ebenfalls unter `public/dokumente/`: Taddis Text
dort kündigt genau diese „Attestvorlagen und Formulierungshilfen“ an. Es sind
Mustertexte für die behandelnde Ärztin oder den Arzt, ohne persönliche Daten.

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
„Was wir gemeinsam bewegen" (bis 27.09.2026 „Unsere Aufgabe") steht weiterhin darüber.

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

- [x] **Wortlaut der Trigger-Warnung.** Geklärt mit KEV-109 (19.10.2026): Der
  Text stammt jetzt von Taddi, unser nüchterner Vorschlag ist ersetzt. Im Panel
  unter „Seiten → Hinweis zu den Inhalten dieser Website".
- [ ] **Wie oft soll die Warnung kommen?** Gebaut ist: einmal pro Besuch. Wer sie
  wegklickt, sieht sie beim nächsten Besuch wieder; wer „nicht mehr anzeigen"
  wählt, nie wieder. Alternative wäre: auf jeder einzelnen Seite erneut. Das
  wäre für Menschen, die viel lesen, schnell zermürbend — deshalb der Vorschlag
  so. Bitte bestätigen.
- [x] **Glossar: die drei Entwürfe freigeben.** Erledigt mit KEV-153: Taddis
  Abkürzungsverzeichnis nennt GdB, Pflegegrad (PG) und Persönliches Budget
  (PB). Sie stehen jetzt mit Abkürzung und Auflösung auf der Seite, unsere
  ungeprüfte Erklärung („ENTWURF — bitte prüfen“) ist weg. Wer eine Erklärung
  ergänzen möchte: Feld „In eigenen Worten“ im Panel, unser alter Entwurf steht
  noch im `GlossarSeeder`.
- [x] **Schreibweise „Killing me Softly".** Geklärt mit KEV-73 (29.09.2026):
  Die Gruppe heißt „Skillin me Softly – Trigger und Skills".
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
  Seit KEV-25 steht er auch sichtbar als „Empfänger" über der IBAN — für alle,
  die von Hand überweisen.
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
- [ ] **Die drei neuen Seiten füllen:** Schutzkonzept, Projekte, Publikationen
  (Beschwerdemanagement ist seit KEV-98 fertig, siehe nächster Punkt, Gremium
  UKFB seit KEV-105, Landesstellen seit KEV-104, Projekte seit KEV-160). Sie liegen im Panel als Entwurf bereit, mit Adresse und
  Gliederung, aber ohne Text. Ins Menü nehmen wir sie auf, sobald sie Inhalt
  haben.
- [ ] **Englische Fassung gegenlesen (seit 08.10.2026 die ganze Seite).**
  Alles ist maschinell übersetzt, jede Seite trägt oben „Machine translation —
  not yet reviewed“ und ist für Suchmaschinen gesperrt. Wer eine Seite geprüft
  hat, nimmt im Panel den Haken „ungeprüft“ heraus; dann bleibt sie, wie sie
  ist. Ändert der Verein den deutschen Text, bleibt die englische Seite beim
  alten Stand, bis jemand die Übersetzung nachzieht. Bitte besonders prüfen:
  - Begriffe: „Satzung“ = *Articles of Association*, „Beitrags- und
    Mitgliederordnung“ = *Fee and Membership Regulations*, „Mitopfer“ =
    *indirect victims*, „Opferbeauftragter“ (Rolle im Verein) = *Victims’
    representative*, „Herr und Frau Unbekannt“ = *Mr and Mrs Anonymous*,
    „Fonds Sexueller Missbrauch“ = *Sexual Abuse Fund* (keine amtliche
    englische Bezeichnung bekannt).
  - Wortspiel „KE!N EINZELFALL“: Der Name bleibt überall stehen („Because you
    are KE!N EINZELFALL!“), das Wortspiel geht auf Englisch verloren.
  - Fristen, Ansprüche und Zuständigkeiten auf den Wissensseiten.
  - Aufgefallen beim Übersetzen: Der Referent heißt einmal „Quendolin
    Winter“, einmal „Quen Winter“ (Das Hilfesystem). Welcher Name stimmt?
    Den Vermerk „ENTWURF — bitte prüfen.“ im Glossar gibt es seit KEV-153
    nicht mehr.
- [ ] **Landesstellen (KEV-104).** Mit Taddis Text veröffentlicht, je
  Bundesland ein Abschnitt. Im Menü unter **Kontakt** (Platz stand nicht im
  Ticket; die Kontaktseite verspricht im Bild schon „Alle Ansprechpartner,
  Landesstellen …“). Bitte bestätigen:
  - Name „Elke **Redeker**“ (Taddi schrieb „Reedeker“, die Adresse auf der
    Kontaktseite lautet redeker@).
  - Gibt es die neuen Postfächer **LS-Bayern@, LS-Berlin@, LS-Hamburg@,
    LS-Sachsen-Anhalt@, LS-Schleswig-Holstein@** schon?
  - Die **Kontaktseite** listet unter „Landesstellen“ noch die persönlichen
    Adressen der Altseite (kuenstler@, redeker@, khalil@, posorske@). Sollen
    dort die neuen LS-Adressen stehen, oder ein Verweis auf diese Seite?
- [ ] **Gremium UKFB (KEV-105).** Mit Taddis Text veröffentlicht, im Menü
  „Verein“ als letzter Punkt (Platz stand nicht im Ticket). Bitte bestätigen:
  Taddi schrieb „kontakt@ufb.org“ und „ukf.org“. ufb.org steht zum Verkauf,
  ukf.org antwortete nicht; die Seite des Kuratoriums ist **ukfb.org** und nennt
  dort selbst **kontakt@ukfb.org**. Das haben wir eingetragen. Die Unterzeile
  auf dem Bild („Unabhängiges Kuratorium für Betroffenenexpertise“) ist von uns.
- [ ] **Schutz- und Wertekonzept und Red Flags (KEV-97).** Zwei Seiten unter
  „Verein“ mit Taddis Text, im Menü an Position 4 und 5 (Red Flags als eigener
  Punkt, wie gewünscht). Beschwerdemanagement rückt dadurch von 5 auf 7. Die
  Seite ist der bisherige leere Entwurf „Schutzkonzept“, jetzt unter
  /schutz-und-wertekonzept. Offen:
  - **Bitte das Schutz- und Wertekonzept als PDF schicken.** Der letzte Satz
    („Hier kannst du … einsehen“) zeigt bis dahin auf nichts. Im Panel als
    Baustein „Dokumente“ anhängbar.
  - Bei Red Flags hängt der Leitfaden der Altseite („6.1.2. Must haves u. Red
    Flaggs für Betroffene“, Stand 01/26). Ist der im Drive neuer, bitte schicken.
  - „Red Flags“ statt „Red Flaggs“ geschrieben, so steht es auch im PDF.
  - Seit 22.10.2026 mit Taddis Bildern aus dem Drive (siehe unten).
- [ ] **Tätigkeits- und Jahresberichte (KEV-103).** Neue Seite unter „Verein“
  mit Taddis Text, im Menü als letzter Punkt (eine Position stand nicht im
  Ticket). Noch ohne Berichte: Der Text kündigt sie ab 2025 an. **Bitte den
  Bericht 2025 als PDF schicken**, oder im Panel selbst als Baustein
  „Dokumente“ anhängen. Seit KEV-147 mit Taddis Bild (Leinenbücher mit „2025“);
  wir haben es rechts um ruhige Wand verlängert, damit die Bücher nicht unter
  dem Titel liegen.
- [ ] **Titelbilder für Schutz- und Wertekonzept (KEV-145) und Red Flags
  (KEV-146).** Die Bilder „Schutz u. Wertekonzept“ und „Red Flaggs“ aus Taddis
  Drive-Ordner „1. Verein“ sind eingebaut. Das Bild zu Red Flags (zwei Türen) haben
  wir rechts um ruhige Wand verlängert, damit die offene Tür nicht unter dem
  Titel liegt. Bitte kurz ansehen; war ein anderes Bild gemeint, lässt es sich
  im Panel tauschen.
- [ ] **Beschwerdemanagement (KEV-98) abnehmen.** Taddis Text in zwei Spalten,
  im Menü „Verein“ an Position 5, darunter ein gemeinsames Formular. Darin
  wählt man oben „Externe Kritik“ (landet wie jede Anfrage im
  Verwaltungsbereich) oder „Interne Beschwerde“ (geht **per E-Mail mit
  Inhalt** an die Ombudsstelle und wird nicht gespeichert, damit der Verein
  Beschwerden über sich nicht selbst liest). Die Knöpfe „Kritik schreiben“ und
  „Beschwerde schreiben“ sowie die Beschreibungen der beiden Wege sind von uns,
  bitte gegenlesen. Zu klären:
  - **Wer liest beschwerdemanagement@kein-einzelfall.de?** Unabhängig ist die
    Ombudsstelle nur, wenn niemand aus dem Verein Zugriff auf dieses Postfach
    hat (auch nicht über die Domainverwaltung). Sonst eine Adresse der
    Ombudsstelle selbst eintragen (`MAIL_OMBUDSSTELLE_AN`).
  - **Auf dem Server echten Mailversand einrichten** (SMTP). Solange
    `MAIL_MAILER=log` gilt, lehnt das Formular ab und verweist auf die Adresse.
  - **Datenschutzerklärung ergänzen:** Beschwerdeformular, Weitergabe per
    E-Mail an die Ombudsstelle, keine Speicherung auf der Website.
  - Taddis Text schrieb „beschwedemanagement@“, wir haben
    „beschwerdemanagement@“ eingetragen. Bitte bestätigen, dass es das Postfach
    so gibt. Ebenso **kritik@kein-einzelfall.de**.
  - Die Unterzeile auf dem Titelbild („Deine Rückmeldung hilft uns, besser zu
    werden.“) ist weiter von uns (A14).
- [ ] **Gruppen & Veranstaltungen (KEV-72).** Der Bereich heißt jetzt so und
  hat eine Übersichtsseite mit Taddis Text. Neu im Menü: **Öffentlichkeitsarbeit**
  und **Rückblick**. Beide sind veröffentlicht, aber noch ohne Text und für
  Suchmaschinen gesperrt (`noindex`). Wenn der Text da ist, im Panel den Haken
  „noindex“ entfernen. Eigene Bilder von Taddi haben inzwischen Gruppen &
  Veranstaltungen (KEV-127), Öffentlichkeitsarbeit (KEV-149) und der Rückblick
  (KEV-137). Offen: Sollen **Aktuelles** und **KE!N EINZELFALL im Dialog** im
  Menü bleiben? Taddis Gliederung nennt sie nicht, sie stehen vorerst dahinter.
- [ ] **Öffentlichkeitsarbeit (KEV-129).** Taddis Text steht, die beiden Sätze
  in Anführungszeichen fett, die Seite ist jetzt für Suchmaschinen offen.
  - In Taddis Vorlage („4. Öffentlichkeitsarbeit“) beginnt der Schlusssatz mit
    „Denn Sichtbarkeit schafft …“, im Ticket ohne „Denn“. Umgesetzt wie im
    Ticket.
- [ ] **Messen auf der Öffentlichkeitsarbeit (KEV-134).** Unter dem Text der
  Abschnitt **„Messen“** mit kleinen Karten (Foto, Datum, Ort, Titel, Anfang
  des Texts). Ein Klick führt auf die eigene Seite der Messe mit dem ganzen
  Text und allen Fotos, z. B. /oeffentlichkeitsarbeit/ehrenamtsmesse-bayreuth-2026.
  Neue Messen (und später Petitionen und Presse) legt der Verein im Panel
  unter **„Öffentlichkeitsarbeit“** an, Fotos lädt er dort selbst hoch. Erste
  Messe: Ehrenamtsmesse Bayreuth mit drei Fotos (aus Taddis Drive, volle
  Auflösung). Zu klären:
  - **Datum:** Im Ticket steht 10.10.2025, eingetragen ist der **10.10.2026**.
    Das ist geklärt: Die Fotos im Drive sind am 10.10.2026 aufgenommen, und an
    dem Samstag war die Freiwilligen-Messe Bayreuth. Nur zur Info für Taddi.
  - **Bildbeschreibungen** der drei Fotos sind von uns und nennen bewusst
    keine Namen. Wenn der Verein sagen möchte, wer zu sehen ist: im Panel beim
    Foto ergänzen.
  - Taddis Vorlage hat für die Ehrenamtsmessen noch einen eigenen
    Einleitungstext („Wir zeigen gern, wer wir sind …“). Der kann im Panel als
    Einleitung über die Karten, sobald er per Ticket kommt.
- [ ] **Presse auf der Öffentlichkeitsarbeit (KEV-136).** Abschnitt „Presse“
  unter den Messen, mit Taddis Einleitung (presse@ als Link). Zwei Einträge mit
  eigener Seite: der stern-Artikel zum Fonds Sexueller Missbrauch und „Unser
  Besuch bei Trude Kuh“. Zu klären:
  - **Datum des stern-Artikels:** Im Ticket 25.05.25, Taddis Drive-Ordner heißt
    „25.06.25 Fonds Sexueller Missbrauch“. Eingetragen ist der **25.06.2025**:
    Der Juni passt zum Ablauf (Antragsstopp im Juni 2025). stern.de ließ sich
    von uns nicht abrufen. Bitte bestätigen, sonst im Panel ändern.
  - **Foto im stern-Artikel:** Taddis Bildschirmfoto zeigt das Foto von
    Tatjana Belmar aus dem Artikel, Bildnachweis „© Broeg“. Das darf auf der
    Website nur mit Erlaubnis des Fotografen bzw. des stern stehen, sonst droht
    eine Abmahnung. Deshalb noch ohne Foto. Liegt die Erlaubnis vor (oder ein
    eigenes Foto), lädt der Verein es im Panel selbst hoch, mit Bildnachweis.
  - **Trude Kuh:** Das Video kommt von Franzi. Bis dahin fehlt Taddis Satz
    „Aber schaut selbst – im Video bekommt ihr den besten Eindruck davon.“, er
    zeigte sonst auf nichts. Stattdessen ein Link zum Beitrag bei Trude Kuh
    TELEVISION; dessen Datum (09.12.2025) steht als Datum, Taddi nannte keins.
    Für das Video fehlt noch ein Feld (eigene Datei oder Zwei-Klick-Einbettung).
- [ ] **Petitionen auf der Öffentlichkeitsarbeit (KEV-135).** Abschnitt
  „Petitionen“ zwischen Messen und Presse, Taddis Einleitung mit ihrem Bild
  (Unterschriftenliste) daneben. Darunter „Initiierte Petitionen“ mit der
  eigenen Petition auf openPetition und „Unterstützte Petitionen“ mit der von
  Tour41, je mit eigener Seite. Zu klären:
  - Überschriften: „Initiierte Petitionen“ (im Ticket „Initiitiert“) und
    „Unterstützte Petitionen“ in der Mehrzahl wie die erste Gruppe (im Ticket
    „Unterstützte Petition“).
  - **Titel der Tour41-Petition** ist von uns, aus der Petition übernommen:
    „‚Fonds Sexueller Missbrauch‘ muss erhalten bleiben!“. Der Link führt wie
    bei Taddi zu Tour41 (Neuigkeiten und Weg zur Petition), nicht direkt zu
    innn.it. Ob dort noch gesammelt wird, ließ sich nicht prüfen.
  - Eigene Petition: Datum 05.10.2026 ist der Start auf openPetition (läuft
    bis 04.04.2027). Knopf „Petition unterschreiben“ ist von uns. Nach dem
    Ende bitte im Panel umbenennen oder den Link entfernen.
- [ ] **Ehrenamtsmesse Lübeck (KEV-133).** Zweite Messe, Taddis Text, der Dank
  am Ende fett; „in Lübeck“ wie im Ticket (ihre Vorlage hatte „in Berlin“).
  Datum 06.09.2026 passt zu den Fotos. Von sechs Bildern im Drive-Ordner
  „Lübeck“ stehen vier auf der Seite (Stand, Halle von oben, Lübeck übers
  Wasser, Gebäude). Zu klären:
  - **Nicht dabei** sind zwei fast gleiche Aufnahmen aus der Halle. Auf einer
    ist ein fremder Mann groß und erkennbar im Vordergrund; ohne seine
    Einwilligung besser nicht veröffentlichen. Der Verein kann sie im Panel
    ergänzen, wenn er es anders sieht.
  - Das Standfoto ist ein Bildschirmfoto mit nur 796 px Breite. Gibt es das
    Original, wirkt es auf der Seite schärfer (im Panel austauschen).
- [ ] **Ehrenamtsmesse Berlin (KEV-132).** Dritte Messe, Taddis Text (im Ticket
  nichts in Anführungszeichen, deshalb nichts fett). Laut Plakat im Drive war es
  die **19. Berliner Freiwilligenbörse** am Roten Rathaus und Neptunbrunnen;
  der Titel bleibt „Ehrenamtsmesse Berlin“ wie bei den anderen, im Panel
  änderbar. Die Seiten der Messen sind jetzt Galerien: Klick auf ein Foto zeigt
  es groß, mit Blättern. Zu klären:
  - **Sechs von vierzehn Fotos** aus dem Drive-Ordner „Berlin“. Viele sind
    Bildschirmfotos aus Instagram-Storys. Nicht dabei: das Plakat und ein
    Rathaus-Foto mit Story-Aufklebern, drei Doppelungen, ein Foto mit gut
    erkennbarem fremdem Gesicht, eines mit einer markierten Privatperson
    (@…), eines vom Stand eines anderen Ausstellers. Beim Teamfoto sind die
    Story-Ränder abgeschnitten. Gibt es die Originalfotos (ohne Story), wären
    sie schärfer.
- [ ] **Abschnitt „Ehrenamtsmessen“ (KEV-131).** Der Abschnitt mit den Messen
  heißt jetzt „Ehrenamtsmessen“ wie in Taddis Ticket (vorher „Messen“), mit
  ihrer Einleitung und ihrem Bild (leerer Messestand) daneben, wie bei den
  Petitionen. Die Überschrift ist im Panel änderbar.
- [ ] **Atteste (KEV-144).** Neue Seite unter „Wissen“ mit Taddis Text, der
  Satz in Anführungszeichen fett, und ihrem Bild aus dem Drive-Ordner
  „3. Wissen“. Das Bild haben wir rechts um ruhige Wand verlängert, damit der
  Papierstapel nicht unter dem Titel liegt. Im Menü steht die Seite vor dem
  Glossar, weil das Ticket keine Position nennt. Darunter hängen die **27
  Attestvorlagen der Altseite** (Reihe 6.5.1, Stand 01/26). Sie lagen dort in
  der Mediathek, waren aber nirgends verlinkt. Bitte prüfen:
  - Sollen alle 27 öffentlich sein? Im Panel lassen sich einzelne entfernen
    oder durch neuere Fassungen ersetzen.
  - Die Titel in der Liste sind Taddis Kurznamen aus der Mediathek. Tippfehler
    haben wir korrigiert („Begeitperson“, „Alltaghsbeeinträchtigungen“,
    „Teilhabebeeinträchtigng“), unklare Namen ergänzt („Maske“ →
    „Maskenpflicht, Befreiung“, „Geschlechtsauswahl“ → „Geschlecht der
    behandelnden Person“). In den Dateinamen stehen die Tippfehler weiter, das
    sieht man nur in der Adresse.
- [ ] **Raum für Ungesagtes und Unüberhörbar (KEV-159).** Neuer Bereich im
  Menü nach „Wissen“, wie in Taddis Drive-Ordner („4. Raum für Ungesagtes“).
  Unüberhörbar mit ihrem Text: Die Adresse sichtbarkeit@kein-einzelfall.de und
  der letzte Satz sind fett, die Adresse ist ein Mailto-Link. Beide Seiten mit
  ihren Bildern aus dem Drive (Tonbandgerät, offene Tür), rechts um Wand
  verlängert. Offen:
  - ~~Text für die Bereichsseite /raum-fuer-ungesagtes~~: kam mit KEV-156,
    „Deine Stimme“ steht jetzt als Unterzeile auf dem Bild.
  - **Wie sollen die Musikstücke auf die Seite?** Audiodateien hochladen geht
    bisher nicht. Schon jetzt möglich: der Baustein „Eingebetteter Inhalt
    (2-Klick)“ mit einem Stück bei SoundCloud, YouTube o. Ä., geladen erst nach
    Klick. Alternativ bauen wir einen Audio-Baustein mit Hochladen im Panel.
    Bitte sagen, was geplant ist.
  - **Kopfzeile:** Mit sechs Bereichen passt die Menüleiste nicht mehr neben
    Logo und Notausgang. Sie steht auf großen Bildschirmen jetzt in einer
    zweiten Zeile darunter, sonst gäbe es überall nur noch das Burger-Menü.
- [ ] **Wall of Shame (KEV-157), bitte ansehen.** Erste Unterseite unter „Raum
  für Ungesagtes“ mit Taddis Einleitung (fett wie im Dokument) und allen 145
  Sätzen. Gebaut nach ihrem Wunsch: ihre rissige Wand mit Papierstapel, jeder
  Klick auf den Stapel zeigt den nächsten Satz auf einem Zettel und setzt einen
  Zählstrich, immer vier und ein Querstrich. Mit jedem Satz wird die Wand
  dunkler und voller, am Ende kommt ihr Satz „Nicht die Betroffenen sollten sich
  für diese Aussagen schämen müssen!“. Wächst die Liste, werden die Striche
  kleiner, die Seite nicht länger. Alle Sätze stehen zusätzlich als Liste zum
  Aufklappen darunter (ohne JavaScript, für Vorlesehilfen, für die Suche).
  Pflegen: Baustein „Wall of Shame“, ein Satz pro Zeile. Zu klären:
  - Reihenfolge: wie im Dokument. Soll es von harmlos nach bösartig gehen,
    reicht es, die Zeilen im Panel umzustellen.
  - Korrigiert: „Gutachter“ → „Gutachtern“ in der Einleitung.
  - ~~Satzrest „Platz hatte, darf hier einen bekommen.“~~: Gehört auf die
    Bereichsseite, KEV-156 bringt ihn ganz („Was zu lange keinen Platz hatte,
    darf hier einen bekommen.“).
  - Ihr Vorschlag mit Absperrbändern ist nicht umgesetzt, die Zählstriche
    tragen die Idee. Ihr Bild „Mauer“ ist noch frei.
- [ ] **Raum für Ungesagtes: Text, Triggerwarnung, Kacheln, Zähler (KEV-156),
  bitte ansehen.** Die Übersicht hat Taddis Text und ist jetzt für
  Suchmaschinen offen. Aufbau: ihr Text, darunter die vier Kategorien als
  Kacheln mit Bild, darunter ihr Aufruf mit Adresse (fett, Mailto-Link) und
  Schlusssatz (fett). In einem Stück wäre der Text nach vier Absätzen
  eingeklappt, und gerade Adresse und Schlusssatz lägen hinter
  „Weiterlesen“.
  - **Triggerwarnung:** ihr Text wörtlich, vor der Übersicht und jeder Seite
    darunter. Sie kommt nach dem allgemeinen Hinweis, nicht gleichzeitig.
    Bestätigt gilt sie für den Besuch, beim nächsten Besuch kommt sie wieder;
    ein „nicht mehr anzeigen“ gibt es bewusst nicht. Drei Wege: weiter, zurück
    zur Startseite, Notausgang. Pflegen: Seite „Raum für Ungesagtes“, Abschnitt
    „Triggerwarnung für diesen Bereich“; leer = keine.
  - **Vorschlag:** Die Warnung nennt Suizidalität und Selbstverletzung, aber
    anders als der allgemeine Hinweis keine Hilfe. Soll ein Satz dazu, etwa
    „Wenn es dir gerade nicht gut geht: Die TelefonSeelsorge (116 123) ist rund
    um die Uhr erreichbar.“? Im Panel ein Absatz mehr.
  - **Zähler (ihre Frage):** Ja, auf jeder Kachel steht „… Aufrufe“, im Panel
    zusätzlich die Spalte „Aufrufe“ in der Seitenliste (jede Seite). Gezählt
    wird auf dem Server nur die Zahl: kein Cookie, nichts über die Person,
    keine Einwilligung nötig. Suchmaschinen und angemeldete Vereinsmitglieder
    zählen nicht, neu laden zählt neu. Steht eine Seite bei null, fehlt die
    Zeile. Abschalten: Baustein „Unterseiten als Kacheln“, Schalter „Aufrufe
    anzeigen“. Beim Livegang beginnt die Zählung bei null.
  - **Von uns:** die Überschrift „Was du hier findest“ über den Kacheln und die
    Unterzeile der Wall of Shame („Sätze, die Betroffene zu hören bekommen
    haben“). Die übrigen Unterzeilen sind ihre Beschreibungen aus dem Ticket
    („Gedichte, Briefe und Texte von Betroffenen“, „Bilder von Betroffenen“,
    „Musikstücke von Betroffenen“); sie stehen jetzt auch auf den Titelbildern
    der Seiten.
  - **Korrigiert:** „etwas Sichtbar machen“ → „sichtbar“; „Texte: Gedichte,
    Briefe, Bilder, Lieder etc.“ → „Texte, Gedichte, …“ (Bilder und Lieder
    sind keine Texte). „Möchtest du uns auch etwas sichtbar machen?“ ist
    stehen geblieben, besser wäre „Möchtest du auch etwas sichtbar machen?“.
  - **Wort für Wort** (ihre Kategorie 2) steht mit ihrem Bild im Menü, aber
    noch ohne Text und deshalb für Suchmaschinen gesperrt. Ihr Bild
    „Word für Word Hintergrund für Texte“ ist noch frei.
- [ ] **Blickwinkel (KEV-158).** Zweite Unterseite unter „Raum für
  Ungesagtes“, gebaut wie Unüberhörbar: Taddis Text, Adresse und letzter Satz
  fett, ihr Bild „3. Blickwinkel“ (Staffelei). Im Menü vor Unüberhörbar, wie
  ihre Nummern im Drive (3. Blickwinkel, 4. Unüberhörbar). Offen wie bei der
  Musik: **Wie sollen die eingesandten Bilder auf die Seite?** Einzelne Bilder
  gehen schon jetzt mit dem Baustein „Text mit Bild“. Eine Galerie mit großer
  Ansicht wie bei den Messen gibt es für Seiten noch nicht, die bauen wir bei
  Bedarf.
- [ ] **Wissen: neuer Text und neues Bild (KEV-143), bitte prüfen.** /wissen
  hat Taddis neuen Text und ihr Bild (Bücherstapel mit aufgeschlagenem Buch).
  - **Fett:** Im Ticket waren die Gänsefüßchen beim Kopieren verloren. Fett
    gesetzt sind „Wissen bedeutet verstehen. Und verstehen bedeutet, selbst
    handeln zu können.“ und der Schlusssatz „Wissen schafft Klarheit. Und
    Klarheit stärkt Selbstwirksamkeit.“ Bitte mit ihrem Original abgleichen.
  - **Weggefallen:** der alte Text samt „Wichtig: Diese Informationen stellen
    keine Beratung dar …“ (ihr neuer Text ersetzt ihn) und „Zu den einzelnen
    Kategorien:“ mit nur zwei Links. Darunter verlinkt „Mehr zu „Wissen““
    alle Unterseiten. Den Hinweis „keine Beratung“ tragen die Seiten Fristen
    und Anträge selbst.
  - Die Unterzeile auf dem Bild („Rechte, Anträge und Hilfesysteme
    verständlich erklärt.“) ist weiter von uns.
- [ ] **Formulare (KEV-250), bitte ansehen und drei Fragen klären.** Taddis
  Formulare aus dem Drive stehen auf /formulare in vier Listen (Mitgliedschaft;
  Vollmacht und Schweigepflichtentbindung; Freigaben; Gruppen und
  Veranstaltungen). Die acht Formulare zum Ausfüllen haben echte Felder
  bekommen: am PC ausfüllen (Acrobat Reader oder Browser), speichern,
  schicken; ausgedruckt sehen sie aus wie bisher. Darüber ein Abschnitt von
  uns, „So nutzt du die Formulare“ (am PC, auf Papier, Unterschrift, wohin).
  - **Wohin schicken?** Wir nennen kontakt@kein-einzelfall.de und die Post.
    Soll der Mitgliedsantrag an verwaltung@ gehen?
  - **Unterschrift:** Für Vollmacht und Schweigepflichtentbindung raten wir
    zu einer eigenhändigen Unterschrift (ausdrucken, unterschreiben, Scan oder
    Foto schicken), weil Behörden und Ärzte eine getippte oft nicht annehmen.
    Bei den Freigaben und dem Mitgliedsantrag reicht laut Text der Name im
    Feld. Bitte bestätigen.
  - **E-Mail und Gesundheitsdaten:** Die Formulare enthalten oft
    Gesundheitsdaten; der Text sagt deshalb, dass E-Mail unverschlüsselt ist,
    und bietet die Post an. Eine sichere Upload-Möglichkeit wäre ein eigenes
    Ticket.
  - **Nicht ausfüllbar, weil zum Lesen:** Gruppenregeln, Leitfäden,
    Ausfüllhilfe, Mitglieds- und Beitragsordnung, und die zwei Teilnahme- und
    Verschwiegenheitserklärungen. Letztere enden mit „Empfohlene Pflichtfelder
    bei der Online-Anmeldung“, sie werden also bei der Anmeldung bestätigt.
    Sollen sie auch zum Unterschreiben sein, fehlen Unterschriftszeilen.
  - **Satzung:** In der Liste steht die von der Seite „Satzung“. Die
    „Satzung II.pdf“ im Drive weicht davon ab (u. a. § 3.2 „Notwendige
    finanzielle Mittel …“ neu, im Namen fehlt „– Opferhilfe für soziale
    Gerechtigkeit“). Welche gilt?
  - **Im Dokument aufgefallen:** Die „Vollmacht“ (ohne
    Schweigepflichtentbindung) schließt mit „Ich erteile diese
    Schweigepflichtentbindung und Einwilligung …“. Vermutlich aus der
    kombinierten Fassung kopiert, bitte im Original korrigieren.
  - Die Word-Fassungen aus dem Drive sind nicht online (PDF reicht für beides).
    Ersetzt der Verein ein Formular im Panel durch ein neues PDF aus Google
    Docs, hat das keine Felder mehr; wir machen es mit `bin/formular-ausfuellbar`
    wieder ausfüllbar.
- [ ] **Formulare (KEV-150).** Neue Seite /formulare unter „Wissen“ zwischen
  Atteste und Fristen mit Taddis Text. Die Formulare selbst fehlen noch:
  Pflegen unter „Seiten“ → „Formulare“, im Baustein „Dokumente“ („Formulare
  zum Herunterladen“) je Formular eine Datei hochladen. Solange er leer ist,
  sieht man ihn nicht. Danach „Von Suchmaschinen ausschließen“ abschalten.
  Hinweis: Der Mitgliedsantrag liegt schon auf /mitgliedschaft.
  Titelbild seit KEV-151 Taddis „4. Formulare“ (Formular mit Vereinslogo).
- [ ] **Projekt § 109 SGG Gegengutachten (KEV-161), bitte gegenlesen.** Neue
  Seite /109-sgg-gegengutachten unter „Projekte“, nach ANUAS (wie eure
  Projektliste: 4.5.12, 4.5.21), mit Taddis Text: „Worum geht es?“, ihre
  Projektdokumentation mit zehn Ständen vom 08.09.2026 zurück bis 17.10.2025,
  „Das Kernproblem“ und das Ziel. Ihre Überschrift steht als Unterzeile.
  **Alle Projekte tragen vorerst das Bild der Projekte-Übersicht** (Wunsch aus
  dem Ticket), auch ANUAS statt des Platzhalters. Eigene Bilder je Projekt
  lassen sich im Panel an der Seite setzen.
  - **Zwei Stellen umformuliert:** „Als Möglichkeiten zur eigenen Suche werden
    Betroffene weiterhin unter anderem auf: [Liste] verwiesen.“ → „Zur eigenen
    Suche verweist das BMAS Betroffene weiterhin unter anderem auf: [Liste]“,
    sonst stünde „verwiesen.“ allein unter der Liste. Im Kernproblem „nach
    § 109 SGG einen bestimmten Arzt benennen zu lassen“ → „**anhören** zu
    lassen“: Benennen tun Betroffene selbst, angehört wird der Arzt vom
    Gericht (so steht es auch in „Worum geht es?“). Bitte bestätigen.
  - Einige kurze Absätze sind zusammengezogen, damit nichts einklappt; die
    drei Nachfragen vom 21.07.2026 stehen weiter als eigene Absätze.
  - Englischer Titel „§ 109 SGG counter-expert opinion“ (von uns).
- [ ] **Projekt ANUAS (KEV-162), bitte gegenlesen und zwei Fragen klären.**
  Neue Seite /anuas unter „Projekte“ (erste Kachel auf der Übersicht) mit
  Taddis Text aus dem Doc „Opferanwalt“: „Worum geht es?“, ihre
  Projektdokumentation mit den Ständen 18.10.2026 bis 23.09.2025 (neuester
  oben), dann Mit-Opfer, Betroffenenexpertise und Ziel. Ihr Projekttitel
  „Opferschutz aus Betroffenenperspektive weiterdenken“ steht als Unterzeile
  unter „ANUAS“.
  - **Die drei PDFs im Ordner sind (noch) nicht auf der Seite:** „Stellungnahme
    Berufsbild Opferanwalt“, „O-Ton Opferanwalt“ und „anonym
    Erfahrungsberichte“ (alle 02/2025, zum „Fachanwalt für Opferrecht“). Gründe:
    1. Der Ordner heißt „1. Opferanwalt“, und in eurer Projektliste sind
       „Opferanwalt“ (4.5.11) und „Opferrechte Gruppe ANUAS“ (4.5.12) zwei
       Projekte. Gehören die PDFs auf eine eigene Seite „Opferanwalt“ oder
       wirklich zu ANUAS?
    2. Briefkopf von Stellungnahme und O-Tönen: alte Adresse (Hummelsbütteler
       Landstraße 96) und zwei Telefonnummern, eine davon „Home“. Unterschrieben
       von Prof. Dr. Carsten Proppe, Tatjana Belmar und A. Hennig. Sollen die so
       online, oder nehmen wir Adresse und Nummern heraus?
    3. Die 50 Erfahrungsberichte tragen Vorname und Initial („Anna S.“). Sind
       das Pseudonyme, und sind alle mit der Veröffentlichung einverstanden?
  - **Bild:** Im Ordner lag keins; seit KEV-161 trägt die Seite das Bild der
    Projekte-Übersicht.
  - **Ehepaar aus Hamburg:** Im Stand 23.09.2025 steht, dass ein Ehepaar
    seinen Sohn bei einem Terroranschlag in Hamburg verloren hat. Das macht
    die beiden womöglich erkennbar; bitte kurz bestätigen, dass sie damit
    einverstanden sind.
  - Kleinigkeiten: „Stand 23.09.2025“ ist der einzige Stand aus 2025, bitte
    prüfen. Ihre Zwischenfrage „Wie können Institutionen überprüfen …?“ steht
    fett im Abschnitt statt als eigene Überschrift, einige kurze Absätze sind
    zusammengezogen, damit nichts einklappt. „ANUAS e. V.“ → „e.V.“.
- [ ] **Projekte (KEV-160), bitte gegenlesen.** Neuer Bereich „Projekte“ im
  Menü nach „Raum für Ungesagtes“ (wie Taddis Drive-Ordner „5. Projekte“), die
  Übersicht /projekte mit ihrem Text und ihrem Titelbild (Hand heftet Zettel
  an eine Wand). Die Seite war bisher ein leerer Entwurf mit den Überschriften
  „Laufende Projekte“ und „Abgeschlossene Projekte“; die sind weg.
  - **E-Mail-Adresse:** Im Text stand **arbeitsgruppen@**kein-einzelfall.de.
    Überall sonst auf der Website (Fußzeile, jede Arbeitsgruppe) steht
    **arbeitsgruppe@** ohne „n“. Wir haben die bekannte Adresse genommen.
    Bitte klären, welches Postfach es gibt, sonst kommen die Mails nicht an.
  - **Absätze und Hervorhebung:** Der Text kam in einer Zeile. Absätze von uns,
    „Du hast eine eigene Projektidee?“ als Überschrift des Aufrufs,
    „Für Sichtbarkeit, Gehör und Unterstützung.“ fett, „Das Team von KE!N
    EINZELFALL“ in Handschrift wie auf „Gruppen & Veranstaltungen“.
    Korrigiert: Anführungszeichen „…“, Doppelpunkt und großes „Auf“ vor dem
    ersten Zitat, kein Komma vor „oder auf die Beine stellen“. „grad“ und
    „Boah“ bleiben, das ist ihr Ton.
  - **Unterzeile** auf dem Bild ist weiter unser „Woran wir gerade arbeiten.“
    (A14). Wenn Taddi eine eigene möchte, im Panel an der Seite ändern.
  - **Die Projekte selbst:** Jedes Projekt wird eine eigene Seite. Anlegen
    kann der Verein sie im Panel; ins Menü unter „Projekte“ trägt sie bisher
    noch die Technik ein (`config/navigation.php`). Sobald dort eine steht,
    erscheint sie auf der Übersicht als Kachel („Unsere Projekte“) mit ihrem
    Titelbild, zwischen Text und Aufruf. Seit KEV-162 steht dort ANUAS.
- [ ] **Rückblick: Einleitung und Titelbild (KEV-137), bitte gegenlesen.**
  /rueckblick hat oben Taddis Einleitung („Manche Begegnungen, Projekte und
  Veranstaltungen wirken noch lange nach. …“) und ihr Titelbild (Fotoalbum)
  statt des Platzhalters.
  - **Absätze:** Der Text kam im Ticket in einer Zeile an. Wir haben vier
    Absätze gesetzt (je zwei Sätze, dann die zwei Schlusssätze einzeln). Wenn
    ihr Original anders gegliedert ist oder etwas fett sein soll, bitte sagen.
  - **Beschreibung für Suchmaschinen** ist jetzt ihr Satz „Hier findest du
    Eindrücke von …“ statt unseres „zuletzt Trauma, Bindung und Beziehung …“,
    das mit jeder neuen Veranstaltung veraltet wäre.
- [ ] **Rückblick: Veranstaltungen rund um Assistenzhunde (KEV-138), bitte
  ansehen.** Neuer Abschnitt auf /rueckblick mit Taddis Text aus „Assistenzhunde
  Veranstaltung“ und ihrem Bild (Frau mit Hund im Gegenlicht). Er steht
  zwischen „Trauma, Bindung und Beziehung“ und FSM (neueste Veranstaltung
  oben); dafür steht das FSM-Bild jetzt rechts und das von aus-unserer-sicht
  links, damit die Bilder weiter abwechseln. Offen:
  - **Videos:** Taddi schreibt, dass es zu beiden Teilen Videoaufnahmen gibt,
    die Franzi noch bearbeitet. Wenn sie fertig sind: wohin damit (YouTube,
    eigene Datei), und dürfen alle Gezeigten öffentlich zu sehen sein?
  - **Datum von Teil II** fehlt im Text. Wenn gewünscht, ergänzen wir es.
  - **Name:** „Ullrich Horn von der Spürnasenakademie“ steht so wie bei ihr.
    Im Netz war dazu nichts zu finden; bitte Schreibweise (Ullrich/Ulrich) und
    Namen der Akademie kurz bestätigen.
  - Ihre Zwischenüberschriften „Teil I“ und „Teil II“ stehen fett wie bei ihr,
    ihre Aufzählungen als Listen, ihr Fettes ist fett.
- [ ] **Rückblick: Zusammenarbeit mit aus-unserer-sicht (KEV-140), bitte
  ansehen.** Dritter Abschnitt auf /rueckblick mit Taddis Text und ihrem Bild
  „Aus unserer Sicht“, darunter die zwei Präsentationen als PDF (öffnen sich im
  Browser Folie für Folie). Zu klären:
  - **Datum:** Im Text stand „Fachtag im November 2025“, auf beiden
    Präsentationen und im Protokoll steht 30.11.2024 (Workshop „ein knappes
    Jahr später“ nach dem 01.01.2024). Auf der Seite steht jetzt **November
    2024**. Bitte bestätigen.
  - **Namen:** Auf der Workshop-Präsentation standen neben Taddi die Vornamen
    der Person für Chat/Hintergrund und der Protokollantin. Die sind
    rausgenommen, damit niemand ungefragt mit einer AG für Betroffene in
    Verbindung gebracht wird. Wenn beide einverstanden sind, setzen wir sie
    wieder ein.
  - **Nicht veröffentlicht:** „Fachtagung Protokoll aG SGB.pdf“ und
    „Zusammenfassung der AG SER.docx“ aus dem Ordner. Beides sind interne
    Arbeitspapiere mit vollen Namen und persönlichen Einschätzungen; das
    Ticket nannte nur die Präsentationen.
  - Überschrift „Präsentationen vom Fachtag am 30.11.2024“ und die Titel der
    zwei Dateien sind von uns. „e. V.“ → „e.V.“, die drei Zeilen zum
    Workshop-Titel sind ein Absatz, ihr Fettes ist fett.
  - Im selben Drive-Ordner liegen „0 Rückblick“ und „0 Rückblick II“ (vermutlich
    das Titelbild der Seite, seit KEV-137 eins davon eingebaut) und
    „Assistenzhunde“ (seit KEV-138 eingebaut).
- [ ] **Rückblick: Veranstaltungen zum FSM (KEV-141).** Zweiter Abschnitt
  auf /rueckblick mit Taddis Text und dem „FSM“-Bild (links). Lange Abschnitte
  klappen jetzt nach vier Absätzen ein („Weiterlesen“), auch der zu „Trauma,
  Bindung und Beziehung“. Ihr P.S. steht als Kasten „Ablehnungsbescheid
  bekommen?“ darunter (Überschrift von uns); „LINK zu den Widersprüchen“ ist
  ein Link auf die Dokumentenliste der FSM-Seite (Musterwidersprüche und
  Stellungnahmen). Korrigiert:
  - „… gearbeitet worden war und das ab sofort keine weiteren Neuanträge mehr
    möglich waren“ → „… mit einer deutlich späteren Antragsfrist, dem
    31.08.2025, gearbeitet worden war –, und dass ab sofort …“
  - „So setzen wir im November 2025 …“ → „setzten“ (Vergangenheit wie davor)
  - Komma vor „und orientierten uns an der Antragsfrist“,
    „Ausfüllhilfe-Veranstaltung“, „behinderungsbedingten“ klein,
    Anführungszeichen „FSM vs. offene Fragen“.
  - „Gemeinsam KE!N EINZELFALL.“ fett wie auf ihren anderen Seiten.
- [ ] **Rückblick: Trauma, Bindung und Beziehung (KEV-142).** /rueckblick hat
  den ersten Abschnitt mit Taddis Text und dem Bild mit den zwei Stühlen,
  daneben bzw. auf dem Handy darüber. Absätze von uns gesetzt, der Name der
  Reihe am Ende fett. Korrigiert: Komma in „Mit den Fragen, die Menschen, die
  mit den Folgen von Trauma leben, beschäftigen.“ (der Satz bleibt ein
  Nachsatz ohne Verb; vielleicht lieber an den Satz davor hängen?). Die Seite
  ist jetzt für Suchmaschinen offen. Das Titelbild oben ist noch der
  Platzhalter. Nächste Veranstaltungen: im Panel unter „Seiten“ →
  „Rückblick“ einen weiteren Baustein „Text mit Bild“ anlegen.
- [ ] **Fristen und Möglichkeiten (KEV-152), bitte ansehen.** Neue Seite
  /fristen unter „Wissen“ (nach „Atteste“), gebaut nach Taddis Vorschlag:
  Einleitung, zwei Kacheln (links „Mögliche Fristen“, rechts „Frist bekommen –
  Was jetzt?“), die zu ihrem Bereich weiter unten springen. Bei den Fristen
  oben die Suche „Welche Frist suchst du?“, darunter A–Z, darunter alle 19
  Fristen als aufklappbare Karten mit Schlagworten; dann ihr Leitfaden in acht
  Schritten und „Noch wichtig“. Bild: ihr „5. Fristen“ (Sanduhr).
  - **Pflegen:** Die Fristen stehen im Panel unter „Fristen“ (neu anlegen,
    ändern, als Entwurf zurückhalten); Einleitung, Leitfaden und „Noch
    wichtig“ unter „Seiten“ → „Fristen und Möglichkeiten“.
  - **Von uns:** die Schlagworte an jeder Frist (aus ihren Beispielen
    Behörde, Gericht, Widerspruch, Antrag, dazu Klage und Bescheid), die
    Überschrift „Was du hier findest“ über den Kacheln und deren Linktexte
    („Zur Fristenübersicht“, „Zum Leitfaden“).
  - **Umgestellt, ohne Wortänderung:** In „Fristverlängerung“ und
    „Überprüfungsantrag“ standen „… und …“ als einzelne Zeilen, jetzt ein Satz
    mit Aufzählung. Ihr „Wichtig: … keine Rechtsberatung“ steht als
    hervorgehobener Kasten. Die Schritte ohne Punkt am Ende der Überschrift.
  - **Korrigiert:** „Kontakt@kein-einzelfall.de“ → klein (ist ein Link),
    doppeltes Leerzeichen in „Frist bekommen – Was jetzt?“.
  - **Inhaltlich** sind es ihre Angaben. Die genannten Paragrafen (§§ 84, 87,
    88, 66, 151, 164, 173 SGG, §§ 25–27, 44 SGB X) passen zu dem, was dort
    steht; eine Rechtsprüfung ersetzt das nicht.
- [ ] **Abkürzungen im sexuellen Kontext (KEV-154), bitte ansehen.** Seit
  KEV-256: Triggerwarnung von Taddi bestätigt („genial“), Unterzeile „Kürzel
  verstehen, benennen und darüber sprechen.“, grüne Überschrift „Was hinter den
  Abkürzungen in Sexarbeit und Beratung steckt“, „noch“ aus „dessen Begriffe
  einem noch nicht vertraut sind“ gestrichen.
  Drittes Glossar als eigene Seite unter „Wissen“, im Menü zwischen „Glossar“ und
  „Behörden & Bescheide“ (Taddis Nummern 1, 2, 3): ihre Einleitung, alle 109
  Abkürzungen und ihr Bild (Buchstabenwürfel) aus dem Drive, rechts um Wand
  verlängert. Pflegen wie die anderen Glossare.
  - [x] **Triggerwarnung, Text von uns:** Bei Taddi steht über der Liste nur
    „TRIGGERWARNUNG“. Die Warnung kommt jetzt vor der Seite, gebaut wie die
    vor „Raum für Ungesagtes“ (einmal pro Besuch, weiter / Startseite /
    Notausgang): „Auf dieser Seite werden Abkürzungen und Begriffe aus
    Sexdienstleistung, Sexualität und BDSM erklärt. Manche Erklärungen
    beschreiben sexuelle Praktiken sehr direkt. Einzelne Begriffe können an
    sexualisierte Gewalt, Ausbeutung oder andere belastende Erfahrungen
    erinnern. Bitte achte auf dich und entscheide selbst, ob und wann du diese
    Inhalte lesen möchtest.“ **Von Taddi bestätigt (KEV-256).**
  - **Aufgeteilt:** Jede Zeile in Auflösung (neben der Abkürzung) und
    Erklärung (darunter), am ersten Doppelpunkt, sonst am Semikolon. Bei
    BulRum/RumBul und GF3 steht deshalb die Beschreibung als Auflösung.
  - **Korrigiert:** „KE!N EINZELFALL e. V.“ → „e.V.“ wie überall.
  - **Zu klären:** Die Seite ist für Suchmaschinen offen wie die anderen
    Glossare. Damit kann sie auch bei Suchen nach Szenebegriffen auftauchen;
    falls das nicht gewollt ist, im Panel „Von Suchmaschinen ausschließen“.
- [ ] **Gericht & Verfahren (KEV-259), bitte ansehen.** Neues Glossar unter
  „Wissen“, im Menü zwischen „Behörden & Bescheide“ und „Medizinische Berichte
  & Gutachten“ (Taddis 3 bis 6 jetzt in ihrer Reihenfolge): ihr Bild
  (Richterhammer und Akten, rechts verlängert), Unterzeile „Damit juristische
  Begriffe verständlicher werden“, grün „Begriffe aus Widerspruchs-, Klage- und
  Gerichtsverfahren für dich“, ihre Einleitung, 87 Begriffe und 53 typische
  Sätze.
  - **Angeglichen** (Kevin, 25.10.2026): In ihrem Dokument fehlte am Ende der
    Einleitung „Gemeinsam KE!N EINZELFALL“, und „Fehlt Dir hier ein Begriff?“
    war nicht fett. Jetzt wie in den anderen Glossaren.
  - **Doppelt:** 21 Begriffe stehen auch in anderen Glossaren, die meisten in
    „Behörden & Bescheide“ (Akte, Antrag, Frist, Widerspruch, Zustellung …),
    hier mit der Erklärung fürs Gerichtsverfahren.
  - **Typische Sätze:** Kategorie eingesetzt als „aus *Klage-, Gerichts- und
    Rechtsmittelverfahren*“ (wie in ihrer Einleitung).
  - **Von uns:** „Gutachten nach § 109 SGG“ verweist mit „Zu unserem Projekt
    § 109 SGG“ auf die Projektseite (Felder „Mehr dazu“ am Eintrag).
- [ ] **Medizinische Berichte & Gutachten (KEV-260), bitte ansehen.** Neues
  Glossar unter „Wissen“, im Menü vor „Sozialrecht & Hilfesystem“: Taddis Bild
  (Mappe mit Stethoskop, rechts verlängert), Unterzeile „Befunde verstehen.
  Gutachten besser einordnen.“, grün „Was medizinische Berichte wirklich
  sagen“, ihre Einleitung, 115 Begriffe und 60 typische Sätze.
  - **Weggelassen:** „Einfach erklärt:“ und die Zeile „3. Medizinische Berichte
    und Gutachten“ über dem Text. „Narkoseprotokoll“ stand bei ihr am Ende,
    die Liste sortiert selbst.
  - **Doppelt:** Erwerbsfähigkeit, Erwerbsminderung, Medizinischer Dienst,
    Rehabilitationsbedarf, Stellungnahme und Teilhabe stehen auch in anderen
    Glossaren, hier mit ihrer Erklärung für dieses Thema.
  - **Typische Sätze:** Kategorie eingesetzt als „aus *Arztbriefen,
    Befundberichten und Gutachten*“. Bitte bestätigen.
- [ ] **Sozialrecht & Hilfesystem (KEV-261), bitte ansehen.** Neues Glossar
  unter „Wissen“, im Menü nach „Behörden & Bescheide“: Taddis Bild (Kasten mit
  Registern, rechts um Wand verlängert), darunter die Unterzeile „Leistungen
  verstehen. Zuständigkeiten einordnen. Unterstützung finden.“, über ihrer
  Einleitung grün „Zwischen Leistungsträgern, Teilhabe und Pflege den
  Überblick behalten.“, dann 99 Begriffe mit Suchfeld und darunter ihre 61
  typischen Sätze. Pflegen wie die anderen Glossare.
  - **Weggelassen:** „Einfach erklärt:“ vor jeder Erklärung (wie bei
    „Behörden & Bescheide“) und die Zeile „4. Sozialrecht und Hilfesysteme“
    über dem Text (bei ihr im Ordner Nummer 6).
  - **Doppelt:** Antrag, Anspruch, Auskunft, Bedarf, Persönliches Budget,
    Pflegegrad und Zuständigkeit stehen auch in anderen Glossaren, hier mit
    Taddis Erklärung für dieses Thema.
  - **Typische Sätze:** Kategorie eingesetzt als „aus *dem Sozialrecht und aus
    verschiedenen Hilfesystemen*“ (wie in ihrer Einleitung).
- [ ] **Typische Sätze (KEV-262), bitte ansehen.** Taddis „7. Typische Sätze –
  einfach übersetzt“ ist keine eigene Seite, sondern steht wie gewünscht unter
  dem Glossar der jeweiligen Kategorie. Auf /behoerden-und-bescheide jetzt
  unter den 128 Begriffen: ihre Einleitung und 47 Sätze, je als Karte mit
  „Einfach übersetzt:“. Unter der Buchstabenleiste führt „Zu den typischen
  Sätzen“ dorthin, direkt verlinkbar als /behoerden-und-bescheide#typische-saetze.
  Pflegen: unter „Seiten“ → „Behörden & Bescheide“, Baustein „Typische Sätze –
  einfach übersetzt“ (Einleitung, Sätze einzeln).
  - **Von uns eingesetzt:** Ihr Platzhalter „XXXX Kategorie einsetzen XXX“ heißt
    hier „aus *Bescheiden und Behördenschreiben*“ (wie in der Einleitung der
    Seite), fett wie bei ihr. Bitte bestätigen oder Wunschwort nennen.
  - **Korrigiert:** „Dies stellt keine Rechtsberatung *da*“ → „*dar*“.
  - **Du groß:** Die Sätze schreiben „Du/Dein“ groß wie Taddis Dokument, die
    Einleitung der Seite darüber klein („du“). Soll das vereinheitlicht werden?
  - **Englisch:** Der Satz bleibt deutsch (so steht er im Brief), übersetzt ist
    nur die Erklärung; ein Satz in der Einleitung sagt das. Ungeprüft.
  - Die übrigen Blöcke: Gericht & Verfahren (53) siehe KEV-259, Medizinische
    Berichte & Gutachten (60) KEV-260, Sozialrecht & Hilfesystem (61) KEV-261.
- [ ] **Behörden & Bescheide (KEV-155), bitte ansehen.** Zweites Glossar als
  eigene Seite unter „Wissen“, im Menü direkt nach „Glossar“: Taddis
  Einleitung (Fettes wie im Dokument, die Adresse als Mailto-Link) und alle
  128 Begriffe mit Erklärung, darüber das Suchfeld wie im Glossar. Pflegen:
  Einleitung und Titel unter „Seiten“ → „Behörden & Bescheide“, die Begriffe
  unter „Glossar“ (Spalte und Filter „Glossar“ zeigen, wo ein Eintrag steht).
  - **Neu für den Verein:** Jedes Glossar ist eine Seite mit dem Baustein
    „Glossar“, und an jedem Eintrag wählt man „Steht auf der Seite“. Für Taddis
    nächstes („Abkürzungen sexueller Kontext“) braucht es uns nur noch für den
    Menüpunkt.
  - **Weggelassen:** das „Einfach erklärt:“ vor jeder der 128 Erklärungen;
    die Einleitung sagt es einmal. Ebenso die Zeilen „1. Behördendeutsch“ und
    „TEXT:“ über der Einleitung (Arbeitsnotizen?).
  - **Bild, Unterzeile, grüne Überschrift (KEV-258):** Taddis Bild (Schreiben
    mit Stempel, rechts verlängert), darunter „Verstehen, was entschieden
    wurde“, über der Einleitung grün „Damit aus Behördensprache verständliche
    Information wird“.
  - **Reihenfolge:** Die Liste sortiert selbst alphabetisch, Umlaute wie ihr
    Grundbuchstabe („Änderung“ unter A).
  - /glossar sieht fast aus wie vorher, hat jetzt aber wie jede Seite die
    Brotkrumen mit „Wissen“ und am Ende „Mehr zu „Wissen““ und „Du möchtest
    uns etwas mitteilen?“.
- [x] **Fristen (KEV-252):** grüne Überschrift „Damit aus einer Frist keine
  verpasste Chance wird!“ über der Einleitung, wie gewünscht.
- [x] **Atteste (KEV-245):** Die 27 Vorlagen stehen auf großen Bildschirmen
  in zwei Spalten. Ein Klick auf den Titel öffnet das PDF im Browser, daneben
  „Herunterladen“. Gilt für alle Dokumentenlisten der Website.
- [x] **Atteste / „Mehr zu ‚Wissen‘“ (KEV-246):** beantwortet mit KEV-239,
  siehe unten.
- [ ] **Wissen (KEV-239), Rückfrage offen.** Im Menü stehen nur noch Taddis
  Kategorien in ihrer Reihenfolge: Atteste, Formulare und Download, Fristen in
  Anträgen und Verfahren, Klartext statt Fachsprache. „Vorlagen/Schreiben“
  kommt dazu, sobald es die Seite gibt. Die sieben alten Seiten sind **nicht
  mehr im Menü, aber noch online**: Das Hilfesystem, Trauma, Bindung und
  Beziehung, Traumafolgestörungen verstehen und Bürokratie-Labyrinth sind die
  Abende von „KE!N EINZELFALL im Dialog“ und dort verlinkt (25.11.2026 mit
  Anmeldung), auf FSM verweist der Rückblick wegen der Widerspruchsvorlagen.
  Offen: Sollen Erwerbsminderungsrente und Unterstützung ganz offline? Und
  sollen die vier Abende unter „Projekte“ hängen? Offline nehmen geht im
  Panel über „Veröffentlicht am“ leeren.
- [x] **Wissen (KEV-241):** Kasten „Urteile zum Schwerbehindertenrecht“
  (REHADAT) von `/wissen` entfernt, siehe A12.
- [x] **Wissen (KEV-242):** Die Kategorien stehen als Knöpfe rechts neben dem
  Text (Baustein „Ausgewählte Seiten“, Darstellung „Knöpfe“). Unten auf
  `/wissen` steht deshalb kein „Mehr zu ‚Wissen‘“ mehr. KEV-240 („was rechts
  hinkommt“) bleibt laut Taddi offen.
- [x] **Atteste (KEV-243):** Unterzeile „Wenn Worte allein nicht reichen“.
- [x] **Atteste (KEV-244):** grüne Überschrift „Attestmöglichkeiten von KE!N
  EINZELFALL e.V.“ und der Absatz mit kontakt@kein-einzelfall.de vor dem
  fetten Schlusssatz.
- [x] **Kontakt (KEV-266 bis KEV-272):** Taddis Bild (Notizbuch, Stift, Handy),
  Überschrift „Kontaktmöglichkeiten“ mit ihrem Text aus „Aufbau Kontakt“
  statt der Anschrift (die steht weiter unten), Kassenwartin unter
  finanzen@, Sachgebiete in ihrer Reihenfolge. Listen aus kurzen Zeilen
  (Vorstand, Landesstellen, Sachgebiete) heben die erste Zeile nicht mehr
  hervor, auf allen Seiten.
- [x] **Landesstellen (KEV-273):** Unterzeile „Gemeinsam stark – an vielen
  Orten“. Der bisherige Satz „KE!N EINZELFALL ist nicht nur an einem Ort
  zuhause.“ steht dadurch wieder als erster Satz im Text.
- [x] **Anfragen & Austausch (KEV-274 bis KEV-276):** Titel „Anfragen &
  Austausch“, Unterzeile „Was dich bewegt, bewegt auch uns.“, Taddis Texte.
  Die „48 Stunden“ sind raus. „per E-Mail an kontakt@…“: das „an“ von uns
  ergänzt. Die „Freigabe zur Verarbeitung und Speicherung übermittelter
  Unterlagen“ steht unter dem Ablauf (dieselbe PDF wie unter Formulare), die
  Krisendienste mit den Nummern vor dem Formular. Auf der Demo stand
  „Krisendienste“ noch als leere Überschrift aus der Altseite.
  **Rückfrage:** Im Drive liegt für „Anfragen & Austausch“ ein neues Bild
  (Sprechblasen). Soll es das Briefumschlag-Bild ersetzen?
- [x] **Raum für Ungesagtes (KEV-263):** Taddis neue Triggerwarnung,
  wörtlich. In ihrem Dokument ist sie durchgehend fett, deshalb ohne
  einzelne Hervorhebungen.
- [x] **Raum für Ungesagtes (KEV-264):** „Für alles, was zu lange keinen
  Platz hatte“ als grüne Überschrift über dem Text.
- [x] **Kontaktformular (KEV-277):** Taddis Text über dem Formular,
  „(erforderlich)“ statt „(muss ausgefüllt werden)“, auch im
  Beschwerde- und im Anmeldeformular.
- [x] **Formulare (KEV-247):** grüne Überschrift „Die Formulare des KE!N
  EINZELFALL e.V.“ über der Einleitung, wie gewünscht.
- [x] **Formulare (KEV-248):** Unterzeile „Übersichtlich an einem Ort“ unter
  dem Titel, wie gewünscht.
- [x] **Fristen (KEV-251):** Unterzeile „Fristen verstehen. Möglichkeiten
  erkennen. Rechtzeitig handeln.“ unter dem Titel, wie gewünscht.
- [ ] **Beschreibungen fürs Suchergebnis (SEO-Prüfung 28.10.2026), von uns.**
  Die alten waren zu kurz („Antrag auf Mitgliedschaft“) oder zu lang. Neu,
  bitte gegenlesen (im Panel unter „Seiten“ → Feld „Beschreibung“):
  - Verein: „KE!N EINZELFALL e.V. ist ein gemeinnütziger Verein aus Hamburg,
    2024 aus Betroffenheit gegründet: Austausch, Wissen und
    Betroffenenexpertise.“
  - Mitgliedschaft: „Mitglied werden bei KE!N EINZELFALL e.V.: warum es sich
    lohnt, Ausfüllhilfe zum Antrag, Beitrags- und Mitgliederordnung,
    Dokumente zum Herunterladen.“
  - Klartext statt Fachsprache: „Abkürzungen, Begriffe und typische Sätze aus
    Bescheiden, Gerichtsverfahren, Gutachten und dem Sozialrecht verständlich
    erklärt, mit Suche über alles.“
  - Gruppen und Arbeitsgruppen: ihre Kurzbeschreibung plus „Selbsthilfegruppe
    (bzw. Arbeitsgruppe) von KE!N EINZELFALL e.V., online und kostenfrei.“
  - Zu lang (über 160 Zeichen, Google kürzt sie ab) sind noch die von
    Schutz- und Wertekonzept, Arbeitsgruppen, Rückblick, Red Flags,
    Unüberhörbar, Fristen, ANUAS, § 109 SGG, Petitionen und Sozialrecht &
    Hilfesystem. Schaden tut das nicht; wer mag, kürzt sie im Panel.
- [ ] **Klartext statt Fachsprache (KEV-254), bitte ansehen.** Neuer Punkt
  unter „Wissen“ mit Taddis Text, einer Suche über alle sechs Kategorien
  (Begriffe und typische Sätze) und den Kategorien als Kacheln. Im Menü stehen
  die sechs Glossare jetzt darunter statt einzeln unter „Wissen“.
  - **Bild, Unterzeile, grüne Überschrift (KEV-278):** Taddis Bild statt
    Platzhalter, darunter „Verstehen statt verzweifeln“, über ihrem Text grün
    „Damit du weißt, was über dich entschieden wird.“
  - **Umbenannt:** Die Kategorie 1 heißt wie in Taddis Dokument
    „Abkürzungen im Sozialrecht“ statt „Glossar“ (Adresse /glossar bleibt).
  - **Text:** in fünf statt sieben Absätzen (sonst klappte der Gruß unter
    „Weiterlesen“ ein), Wortlaut unverändert.
  - **Von uns:** Überschrift „In allen Kategorien suchen“ und die Zeile
    „Abkürzung, Begriff oder Satz aus deinem Schreiben“ über dem Suchfeld.
  - Die Suche filtert beim Tippen wie in den einzelnen Glossaren.
- [ ] **Glossar / Abkürzungen Sozialrecht (KEV-255).** Unterzeile „Damit aus
  Buchstaben wieder Bedeutung wird.“, grüne Überschrift „Abkürzungen verstehen,
  Zusammenhänge erkennen, sicherer durch das SER finden.“ statt bisher
  „Abkürzungen im Sozialen Entschädigungsrecht (SER)“. Bild und Suchmaske mit
  A–Z waren schon da (das Bild im Drive ist dasselbe). Seit KEV-254 heißt die
  Seite wie bei Taddi „Abkürzungen im Sozialrecht“.
- [ ] **Glossar mit Taddis Abkürzungen (KEV-153).** /glossar zeigt ihre
  Einleitung („Abkürzungen im Sozialen Entschädigungsrecht (SER)“, Fettes wie
  im Dokument) und 195 Abkürzungen mit Auflösung, darüber ein Suchfeld, das
  schon beim Tippen filtert. Taddis Frage im Dokument („Soll das als
  Suchfunktion aufgebaut werden?“) haben wir mit Ja beantwortet: Suchfeld auf
  der Seite, und die Suche der Website findet jetzt auch kurze Abkürzungen
  (AU, KK, SG) und verweist bei vielen Treffern ins Glossar. Pflegen: die
  Einträge im Panel unter „Glossar“ (eine Erklärung ist jetzt freiwillig),
  Einleitung, Titel und Bild unter „Seiten“ → „Glossar“. Das Bild ist Taddis
  „Abkürzungen SER“ (Zettelkasten) aus dem Drive-Ordner „3. Wissen“, rechts um
  Wand verlängert. Bitte prüfen:
  - **Korrigierte Auflösungen** (im Dokument stand etwas anderes): BAS =
    Bundesamt für Soziale *Sicherung* (nicht „für soziale Dienste“); BfA =
    *Bundesversicherungsanstalt für Angestellte*, heute DRV Bund (die
    Bundesanstalt für Arbeit hieß BA); EM = *Erwerbsminderung* (EMR ist die
    Rente); GdS = Grad der *Schädigungsfolgen*; PI = *Polizeiinspektion*
    (Polizeidirektion ist PD); SEK = *Spezial*einsatzkommando; MD =
    Medizinischer Dienst (früher MDK), MDK = Medizinischer Dienst der
    Kranken*versicherung*; INTERPOL = Internationale Kriminalpolizeiliche
    Organisation.
  - **Korrigierte Abkürzungen:** „AuslG“ → *AusglV* (so steht es in Taddis
    Einleitung; AuslG war das Ausländergesetz), „PAVG“ → *PflVG*, „BARMEER“ →
    *BARMER*, „TBI“ → *TBl* (Merkzeichen Taubblindheit), „Bundestags.Drs.“ →
    *BT-Drs.*, „Psych KG“ → *PsychKG*.
  - **Tippfehler** stillschweigend korrigiert, etwa „aussergewöhnliche“,
    „Berufsausübungssgemeinschaft“, „Sondereinsatzkomando“,
    „Pflichtvericherungsgesetz“, „Teilhaberatungsstelle“, „vergleich“; dazu
    Punkte (bzw., ggf., vgl., z. B.), Bindestriche und Kleinschreibung
    vereinheitlicht und die Kürzel in UBSKM und UKASK ausgeschrieben.
  - **Als Entwurf, auf der Seite unsichtbar:** „vi: Seite“ (vi ist uns als
    Abkürzung für Seite nicht bekannt; gemeint „S.“?) und „vVV:
    Verwaltungsvorschrift“ (üblich sind VV oder VwV). Im Panel mit dem Filter
    „Sichtbar → Nur Entwürfe“ zu finden.
  - Doppelt vergeben, beide stehen da: KKH (Krankenhaus und Kaufmännische
    Krankenkasse), VA (Versorgungsamt und Verwaltungsakt).
  - Ergänzt: „1. Kl.“ trägt wie die übrigen Merkzeichen den Zusatz
    „(Schwerbehindertenausweis)“.
  - Die englischen Auflösungen sind maschinell und ungeprüft, die deutsche
    Bezeichnung steht jeweils in Klammern dabei.
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

### A12 · Verweis auf REHADAT (Text von uns) — erledigt

**Seit KEV-241 (27.10.2026) entfernt**, Taddi: „Das gehört da nicht hin.“ Der
Verweis auf der Seite „Grad der Behinderung“ bleibt.


Auf `/wissen` steht seit dem 22.09.2026 ein Hinweis-Kasten „Urteile zum
Schwerbehindertenrecht" mit Verweis auf REHADAT-Recht. Hintergrund in
`docs/KEV-4-REHADAT.md`: REHADAT ist dort erschöpfend, eine eigene Sammlung
daneben wäre Doppelarbeit — beim Sozialen Entschädigungsrecht dagegen ist
REHADAT dünn, dort entsteht später die eigene Bibliothek.

**Frage an den Verein:** Der Text stammt von uns. Bitte gegenlesen — und sagen,
ob der Verweis so gewollt ist. Er ist im Panel unter `/wissen` im letzten
Baustein änder- oder löschbar.

### A13 · Fünf Seiten aus Abschnitt 6.2 — Text von uns, bitte gegenlesen

Die Punkte 6.2.1 bis 6.2.5 des Strukturpapiers („Anträge und Formulare") gab es
weder auf der Altseite noch bei uns. Sie sind jetzt angelegt und mit Text
gefüllt — **das ist die Ausnahme von der Regel**, dass der Verein die Inhalte
stellt. Möglich war es, weil diese Seiten geltendes Recht beschreiben und nicht
den Verein; die Angaben stammen aus amtlichen Quellen, jede Seite nennt ihre.

| Seite | Inhalt |
|---|---|
| `/opferentschaedigungsgesetz` | Wegweiser: Das OEG ist seit 01.01.2024 im SGB XIV aufgegangen |
| `/soziales-entschaedigungsrecht` | SGB XIV, Traumaambulanz, Antrag |
| `/grad-der-behinderung` | GdB, Schwerbehindertenausweis, Feststellungsverfahren |
| `/pflegegrad` | Pflegegrade 1–5, Begutachtung, 25-Arbeitstage-Frist |
| `/persoenliches-budget` | § 29 SGB IX, Zielvereinbarung |

Solange sie ungeprüft sind, gilt dreifacher Schutz: ein **sichtbarer Vermerk**
ganz oben auf jeder Seite, **`noindex`** (ungeprüfte Rechtsauskunft gehört
nicht in eine Suchmaschine) und **kein Menüeintrag**. Erreichbar sind sie über
die Adressen oben.

**Frage an den Verein:** Bitte fachlich gegenlesen. Danach im Panel je Seite
das Häkchen „Noch nicht vom Verein geprüft" entfernen, „Von Suchmaschinen
ausschließen" abschalten und die Seiten in `config/navigation.php` ins Menü
nehmen (macht Kevin).

**Inhaltliche Rückfrage:** 6.2.1 (OEG) und 6.2.2 (SER) sind derselbe
Rechtsbereich in zwei Zeitschichten — das OEG ist seit dem 1. Januar 2024 im
SGB XIV aufgegangen. Deshalb ist die OEG-Seite bewusst kurz und führt auf die
SER-Seite weiter, statt dasselbe zweimal zu erzählen. Der Begriff „OEG" ist
aber so eingeführt, dass die Seite bleiben sollte: Danach wird gesucht. Ist das
so recht, oder sollen die beiden zu einer Seite werden?

---

### A14 · Titelbilder und Unterzeilen — bitte ansehen und gegenlesen

Auf Taddis Wunsch (WhatsApp, 24.09.2026) haben 17 Seiten und die
Terminübersicht ein Titelbild bekommen, KI-erzeugt nach ihrer Bildvorgabe
(siehe `docs/Bildsprache.md`). Auf dem Bild stehen immer Bereich, Titel und
eine kurze Unterzeile. Die meisten Unterzeilen sind Sätze des Vereins, diese
stammen von uns:

| Seite | Unterzeile |
|---|---|
| `/ueber-uns-vorstand-und-team` | Die Menschen hinter unserer Arbeit. |
| `/unterstuetzung` | Wichtiges über Rechte, Anträge und unsere Arbeit. |
| `/anfragen` | Persönlicher Austausch zu Entschädigung, Schwerbehinderung und Pflegegrad. |
| `/wissen` | Rechte, Anträge und Hilfesysteme verständlich erklärt. |
| `/kein-einzelfall-im-dialog` | Wissenschaftliche Erkenntnisse und gelebte Erfahrung im Austausch. |
| `/soziales-entschaedigungsrecht` | Hilfe für Menschen, die durch eine Gewalttat geschädigt wurden. |
| `/traumafolgestoerungen-verstehen` | Was eine Traumafolgestörung ist – und wie man passende Hilfe findet. |
| `/beschwerdemanagement` (noch Entwurf, Bild von Taddi, KEV-80) | Deine Rückmeldung hilft uns, besser zu werden. |
| `/projekte`, `/publikationen` (noch Entwurf) | Woran wir gerade arbeiten. / Umfragen und Veröffentlichungen des Vereins. |

**Frage an den Verein:** Passen Bilder und Unterzeilen? Beides lässt sich je
Seite im Panel unter „Titelbild“ ändern.

---

## B. Technisch vor dem Go-Live (Kevin)

### B0 · Stand der Prüfung vom 29.10.2026

Jeder Punkt aus B1 ist gegen Code und Demo geprüft (Demo nur lesend). Was
erledigt ist, steht unten abgehakt mit Beleg. Neu dazugekommen:

- [ ] **Wohin geht die Live-Seite?** Die Demo läuft auf Nils' Server. Die
      Domain kein-einzelfall.de zeigt noch auf all-inkl (85.13.168.30, alte
      WordPress-Seite). Entscheidung bei Nils und dem Verein.
- [ ] **Beim Umstellen nur den Web-Eintrag ändern.** Die Postfächer des
      Vereins liegen ebenfalls bei all-inkl (MX `w0212ffe.kasserver.com`).
      Nur die A-Einträge für `kein-einzelfall.de` und `www` umstellen, MX und
      alle Mail-Einträge (SPF, DKIM) bleiben. Sonst kommen keine Mails mehr an.
- [ ] **Echter Mailversand.** Auf der Demo steht `MAIL_MAILER=log`: Es geht
      keine Mail raus. Davon hängen Kontakt- und Beschwerdeformular und die
      SHG-Anmeldung mit Zugangslink ab (KEV-107, KEV-279, KEV-283). Vorschlag:
      ein Postfach `noreply@kein-einzelfall.de` bei all-inkl als Absender per
      SMTP. Für KEV-283 will Nils vorher einen Call, wohin welches Formular geht.
- [ ] **Datenbank-Sicherung** auf dem Live-Server, täglich, mit Aufbewahrung
      ausserhalb des Servers. Ohne `APP_KEY` (siehe unten) ist eine Sicherung
      der verschlüsselten Anfragen wertlos, beides gehört zusammen.
- [ ] **Zugangslinks der Selbsthilfegruppen** in `/admin` eintragen (Technik,
      aus Taddis Liste im Drive unter „Selbsthilfegruppen“). Auf der Demo hat
      keine der drei offenen Gruppen einen. Ohne Link bekommt nach der
      Anmeldung niemand einen Zugang. Der Verein selbst kommt seit 30.10.2026
      nur in den Vereinsbereich `/intern` und kann die Links dort nicht ändern.
- [ ] **Konto für den Verein** auf dem Live-Server anlegen:
      `php artisan verein:konto <email> <name>`. Das Passwort steht nur in der
      Ausgabe, auf sicherem Weg an Taddi geben. Anmeldung unter `/intern`.
- [ ] **Englisch: so live oder abschalten?** Alle englischen Seiten sind als
      „ungeprüft“ gekennzeichnet und auf `noindex`. Entweder so veröffentlichen
      oder Englisch im Panel deaktivieren, bis jemand gegengelesen hat (A6).
- [ ] **Rechtstexte:** Entwürfe für Hosting, E-Mail, Microsoft Teams und den
      Bildnachweis liegen in `docs/Entwurf-Rechtstexte-GoLive.md` (siehe die
      beiden Punkte zu Impressum und Datenschutz unten).

### B1 · Muss
- [x] **Hochladen im Panel: Ablage muss einen Deploy überleben.**
      *Geprüft 29.10.2026: Auf der Demo ist `/var/www/html/storage` ein
      Docker-Volume (`kein-einzelfall_ke_storage`). Auf dem Live-Server
      genauso einrichten.* Bilder und
      Dokumente, die der Verein hochlädt, liegen in `storage/app/public/medien`
      (seit 21.10.2026). Der Deploy baut das Image neu und tauscht die
      Container (`/docker/kein-einzelfall/deploy.sh`). Ist `storage/app/public`
      dort kein Volume, sind nach dem nächsten Deploy alle hochgeladenen Dateien
      weg. Bitte auf dem Server prüfen und sonst ein Volume einrichten. Zum
      Prüfen: ein Bild hochladen, deployen, Bild noch da?
- [x] *Demo 29.10.2026: 100M / 100M.* **PHP-Grenzen fürs Hochladen:** `upload_max_filesize` mindestens `20M`,
      `post_max_size` mindestens `25M`. Die Vorgabe von PHP (2 MB) lässt die
      meisten PDFs scheitern. Bilder verkleinert schon der Browser, die kommen
      auch so durch. Die Verknüpfung `php artisan storage:link` ist nicht nötig:
      Fehlt sie, liefert eine Route die Dateien aus (schneller ist sie aber).
- [ ] **`APP_KEY` sichern.** Die Verschlüsselung der Anfragen hängt daran. Geht der
      Schlüssel verloren, sind alle Anfragen unwiederbringlich weg.
- [ ] **`SESSION_SECURE_COOKIE=true`** setzen. *Fehlt auch auf der Demo (29.10.2026).* Steht derzeit auf `NULL`, damit ginge
      das Session-Cookie auch über HTTP raus.
- [x] *Demo hat ein Konto (29.10.2026).* **Admin-Konto von Hand anlegen.** Lokal legt `bin/start` per `AdminSeeder`
      `admin@kein-einzelfall.test` / `kein-einzelfall` an. Der Seeder überspringt
      sich in Produktion selbst — dort also `php artisan make:filament-user` und
      anschliessend `panel_zugang` setzen (siehe README).
- [x] *29.10.2026: keine Termine mehr im Bestand.* **Beispieldaten löschen:** aktuell zwei Termine. Blogbeiträge und Anfragen
      sind derzeit keine im Bestand — vor dem Go-Live gegenprüfen.
- [x] **`/module-demo` entfernen** (interne Vorschau). *Entfernt am 29.10.2026.*
- [x] **`MAIL_ANFRAGEN_AN`** setzen, sonst kommt keine Benachrichtigung an.
      *Auf der Demo gesetzt (29.10.2026). Ankommen wird erst etwas mit echtem
      Mailversand, siehe B0.*
- [x] *Demo: Container `kein-einzelfall-scheduler` (29.10.2026).* **Scheduler einrichten** (`* * * * * php artisan schedule:run`), sonst laufen
      die Löschfristen nie.
- [x] *29.10.2026: Die Listen stehen auf /datenschutz, /spenden, /anfragen
      und /veranstaltungen.* **Aufzählungen der Altseite nachtragen.** Der Importer hat bis 20.09.2026
      keine `<li>` gelesen (siehe `docs/Komponenten.md`, Abschnitt 24). Auf neun
      Seiten fehlen deshalb Listen — unter anderem die Notfallnummern auf
      `/anfragen`, Referent/Datum/Ort auf den Veranstaltungsseiten, die
      „Warum spenden"-Punkte, die Betroffenenrechte im Datenschutz. Der frische
      Abzug enthält sie; nachgetragen wird pro Seite von Hand, nicht per
      Neu-Import, weil die Seiten inzwischen bearbeitet sind. Eigenes Ticket.
- [ ] **Impressum und Datenschutz gegenlesen.** *29.10.2026: Im Impressum steht
      als Bildnachweis noch „Shutterstock“, kein Bild der neuen Seite stammt
      von dort. Entwurf in `docs/Entwurf-Rechtstexte-GoLive.md`.* Beim Übernehmen aus Elementor sind
      aus 21 Roh-Blöcken 13 geworden (der Rest waren Layout-Reste ohne Text). Beim
      Impressum sind es 8 Blöcke mit zusammen nur 40 Wörtern — sieht nach
      zerstückelten Zeilen aus. Bei beiden Seiten ist Vollständigkeit rechtlich
      relevant.
- [ ] **Datenschutzerklärung neu schreiben.** *29.10.2026: teilweise erledigt
      (Cookies, Browser-Speicher, betterplace, Kontaktformular, SHG-Anmeldung).
      Offen: Als Hoster steht noch all-inkl.com, Microsoft Teams fehlt ganz.
      Entwürfe in `docs/Entwurf-Rechtstexte-GoLive.md`.* Die übernommene stammt von der
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

### B3 · SEO auf dem Server (Nils, aus der SEO-Prüfung vom 28.10.2026)

Was im Code geht, ist erledigt (`docs/Komponenten.md`, Abschnitt 48). Das hier
geht nur auf dem Server oder mit Zugängen:

- [ ] **Richtige Adresse in canonical, hreflang, Sitemap und og:image.**
      Laravel baut diese Adressen aus Host und Schema der jeweiligen Anfrage
      (es gibt kein `URL::forceRootUrl`). Hinter dem Proxy muss deshalb der
      öffentliche Host ankommen und `https` erkannt werden (`X-Forwarded-Proto`,
      TrustProxies). Sonst stehen dort interne Adressen oder `http://`. Zum
      Prüfen nach dem Umzug: Quelltext einer Seite, `<link rel="canonical">`
      muss mit `https://kein-einzelfall.de/` beginnen. `APP_URL` trotzdem auf
      `https://kein-einzelfall.de` setzen (Mails, Befehle ohne Anfrage).
- [ ] **Nur eine Adresse:** `http://` und `www.` per 301 auf
      `https://kein-einzelfall.de` (im Webserver, nicht in Laravel).
- [ ] **Demo nicht in den Index:** Solange die Demo hinter dem Passwort liegt,
      passt es. Fällt das Passwort weg, `X-Robots-Tag: noindex` für die
      Demo-Adresse setzen.
- [ ] **Search Console:** Zugang sichern, Ausgangswerte der Altseite sichern
      (siehe A9), nach dem Launch `sitemap.xml` einreichen (siehe B2).
- [ ] **Komprimierung und Zwischenspeicher:** gzip/brotli für HTML, CSS, JS;
      lange `Cache-Control` für `/build/`, `/fonts/`, `/img/` (die Dateien
      tragen Versionen im Namen bzw. `?v=`). Danach einmal PageSpeed Insights
      auf der echten Adresse laufen lassen.

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
- **Drei tote Adressen in der Sitemap der Altseite** (geprüft 22.09.2026):
  `/vereins-news/`, `/hello-world-6/` und der Termin
  `/event/selbsthilfegruppe-retraumatisierung-durch-antragsstellung/` stehen in
  `post-sitemap.xml` bzw. `tribe_events-sitemap.xml`, liefern aber alle 404.
  Google bekommt diese Sitemap — tote Adressen darin kosten Vertrauen. Aus
  unserer Sicht heisst das zugleich: Es gibt auf der Altseite **keinen**
  Blogbeitrag und **keinen** Termin zu übernehmen; unser Bestand ist nicht
  unvollständig, sondern korrekt leer.
