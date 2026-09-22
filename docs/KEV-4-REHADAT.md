# KEV-4 · REHADAT prüfen

Stand 22.09.2026 · geprüft an https://www.rehadat-recht.de

## Das Ticket sagt etwas anderes als das Protokoll

Im Ticket steht: *„Webseite auf Schwerbehindertenrecht überprüfen und in eigene
Datenbank übernehmen."* Das liest sich wie ein Auftrag, fremde Inhalte zu
kopieren.

Im Besprechungsprotokoll vom 02.08.2026 steht wörtlich:

> [Nils Nehring] Rehadat Webseite prüfen: Die bereitgestellte Webseite für das
> Schwerbehindertenrecht analysieren, **um Strukturen für die eigene Datenbank
> abzuleiten.**

Gemeint war also: **abschauen, wie die das machen** — nicht: deren Daten
übernehmen. Das passt zu zwei weiteren Punkten desselben Protokolls:

- Franziska Künstler führt Adresslisten und **Gerichtsurteilsammlungen derzeit
  in Excel-Tabellen mit fester Struktur**, die später in ein Datenbanksystem
  überführt werden sollen.
- Der geplante Bereich **„Bibliothek"/„Wissensdatenbank"** soll *Urteile,
  Gutachten und Gesetzestexte* sammeln.

KEV-4 ist damit die Vorarbeit für die Bibliothek: Bevor wir ein Datenmodell für
Franziskas Urteile erfinden, sehen wir uns an, welche Felder sich bei einem
Angebot bewährt haben, das seit 1989 nichts anderes tut.

## Was REHADAT ist

Ein Informationsangebot des **Instituts der deutschen Wirtschaft Köln e. V.**,
gefördert vom **BMAS** aus dem Ausgleichsfonds. Rund 20 Portale (Hilfsmittel,
Adressen, Werkstätten, Statistik …); für uns zählt **REHADAT-Recht** mit
**16.710 dokumentierten Urteilen** zur beruflichen Teilhabe von Menschen mit
Behinderung, dazu Gesetze, ein Lexikon und Urteile in Einfacher Sprache.

Bemerkenswert für unser Projekt: Das Portal bietet **Leichte Sprache und
Gebärdensprache** an und hat eine eigene Erklärung zur Barrierefreiheit. Als
Referenz für unseren eigenen Anspruch ist es damit ohnehin einen Blick wert.

## Die Struktur, um die es geht

Ein Urteilseintrag bei REHADAT-Recht hat diese Felder (abgelesen an einem
Datensatz, LSG Hessen L 3 SB 80/23):

| Feld | Beispiel | Anmerkung |
|---|---|---|
| Titel | „Zeitliche Zäsur durch neuen Verschlimmerungsantrag: Kein höherer GdB …" | redaktionell, keine Aktenzeichen-Wüste |
| Gericht | LSG Hessen 3. Senat | inkl. Senat |
| Aktenzeichen | L 3 SB 80/23 | |
| Datum | 27.01.2026 | |
| Grundlage | SGB IX § 152 Abs. 1 \| SGB IX § 229 Abs. 2 \| SGG § 103 … | **Normenkette, mehrfach, je Paragraf verlinkt** |
| Leitsatz | Fließtext | |
| Rechtsweg | SG Frankfurt a. M., 24.04.2023 – S 12 SB 207/22 | Vorinstanz als eigener Verweis |
| Quelle | Rechtsprechungsdatenbank Hessen | Herkunftsnachweis |
| Langtext | Tenor, Tatbestand, Entscheidungsgründe | eigener Reiter |
| Ähnliche Urteile | Querverweise | |
| Themenzuordnung | „Erhöhung des GdB" | siehe Klassifikation unten |

Darüber liegt eine **zweistufige Klassifikation** mit sieben Oberkategorien:
Arbeit & Beschäftigung · Aus- & Weiterbildung · Diskriminierung ·
Feststellungsverfahren · Hilfsmittel · Leistungen · Aktuelles.

Drei Entwurfsentscheidungen, die wir übernehmen sollten:

1. **Jede Kategorie trägt einen redaktionellen Erklärtext** („Hintergrund­
   informationen"), bevor die Urteilsliste kommt. Wer nicht weiß, was ein
   Neufeststellungsverfahren ist, versteht sonst keinen einzigen Treffer.
2. **Die Normenkette ist ein eigenes, mehrfaches Feld** — nicht Fließtext. Nur
   so lässt sich später „alle Urteile zu § 152 SGB IX" beantworten.
3. **Der Titel ist eine Aussage, kein Aktenzeichen.** Für Betroffene ist die
   Trefferliste sonst unbenutzbar.

Für die eigene Bibliothek hieße das ungefähr: `urteile` mit den Feldern oben,
`normen` als eigene Tabelle mit Pivot (ein Urteil hat viele Paragrafen, ein
Paragraf viele Urteile), `kategorien` zweistufig, dazu unser bestehendes
`glossary_terms` für die Begriffe. Gutachten und Gesetzestexte kämen als
weitere Typen daneben — das Protokoll nennt sie in einem Atemzug mit den
Urteilen.

## Übernehmen dürfen wir die Inhalte nicht

Und wir sollten es auch nicht wollen:

- **Die Urteilstexte selbst sind frei.** Gerichtsentscheidungen sind amtliche
  Werke nach § 5 UrhG und genießen keinen Urheberrechtsschutz. Wer sie aus der
  Quelle holt (Rechtsprechungsdatenbanken der Länder, `rechtsprechung-im-
  internet.de`), darf sie veröffentlichen.
- **REHADATs Aufbereitung ist es nicht.** Die redaktionellen Titel, die
  Kategorisierung, die Einfache-Sprache-Fassungen und die Verknüpfungen sind
  eigene Leistung. Hinzu kommt das **Datenbankherstellerrecht (§§ 87a ff.
  UrhG)**: Das systematische Auslesen wesentlicher Teile einer Datenbank ist
  auch dann unzulässig, wenn die Einzelinhalte gemeinfrei sind. Genau darauf
  zielt eine „Übernahme in die eigene Datenbank".
- Die `robots.txt` sperrt nur SEO-Crawler (Ahrefs, Semrush, Baidu) und drei
  Verzeichnisse. Das ist **kein** Freibrief — das Datenbankrecht gilt
  unabhängig davon.
- Ein Nutzungs- oder Lizenzvermerk, der eine Nachnutzung erlaubt, steht weder
  im Impressum noch sonst auf dem Portal. Ohne ausdrückliche Erlaubnis gilt:
  alle Rechte vorbehalten.

**Die Struktur abzuschauen ist dagegen unproblematisch** — ein Datenbankschema
ist keine geschützte Leistung. Genau das war laut Protokoll auch der Auftrag.

## Wo REHADAT stark ist und wo nicht

Trefferzahlen in REHADAT-Recht (eigene Messung über die Portalsuche):

| Suchbegriff | Treffer |
|---|---|
| Merkzeichen | 1.914 |
| Schwerbehindertenausweis | 1.690 |
| SGB XIV | 125 |
| Opferentschädigungsgesetz | 51 |
| Gewaltopfer | 21 |

Das ist der strategisch wichtigste Befund: Beim **Schwerbehindertenrecht** ist
REHADAT erschöpfend — eine eigene Sammlung daneben wäre Doppelarbeit, die der
Verein nie aktuell halten kann. Beim **Sozialen Entschädigungsrecht**
(OEG/SGB XIV), dem Kernthema von KE!N EINZELFALL, ist REHADAT dünn: 51 Treffer
gegenüber 16.710 Urteilen insgesamt.

**Dort liegt die Lücke, die der Verein füllen kann** — und zwar als einziger,
weil die Betroffenen und ihre Verfahren ohnehin bei ihm zusammenlaufen.

## Empfehlung

1. **Struktur übernehmen, Inhalte nicht.** Das Datenmodell der Bibliothek nach
   dem Muster oben bauen.
2. **Schwerpunkt Soziales Entschädigungsrecht.** Franziskas Excel-Sammlung ist
   der Startbestand; zum Schwerbehindertenrecht verlinken wir auf REHADAT,
   statt es nachzubauen.
3. **Auf REHADAT verweisen, nicht kopieren.** Ein Verweis kostet nichts, ist
   rechtlich sauber und für Betroffene genauso hilfreich.
4. **Kooperation anfragen.** REHADAT ist öffentlich gefördert und hat einen
   Auftrag zur Verbreitung. Eine Anfrage beim IW Köln (kontakt@rehadat.de), ob
   eine Verlinkung, ein Datenaustausch oder eine gegenseitige Nennung möglich
   ist, ist der Versuch wert — Kooperationen waren laut Protokoll ohnehin als
   Pfeiler der Außendarstellung definiert.

## Offen, bevor gebaut wird

- [ ] **Franziskas Excel-Tabellen einsehen.** Steht schon als Punkt B in der
      Übergabe-Checkliste. Ohne die tatsächlichen Spalten ist jedes Schema
      geraten — und ihre Struktur schlägt im Zweifel REHADATs.
- [ ] **Bestätigen lassen, dass „Strukturen ableiten" gemeint war.** Das Ticket
      sagt etwas anderes als das Protokoll; das gehört geklärt, bevor jemand
      Aufwand schätzt.
- [ ] **Rechtliche Einschätzung, falls doch Inhalte übernommen werden sollen.**
      Die Bewertung hier ist nach bestem Wissen, aber keine Rechtsberatung.
