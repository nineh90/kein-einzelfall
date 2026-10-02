# Bildsprache und Titelbilder

Vorgabe des Vereins (Taddi, per WhatsApp, 24.09.2026): Wo Bilder der Seite
guttun, werden sie nach einem festen Prompt erzeugt, damit alle Bilder wie aus
einem Fotoshooting wirken.

## Die Vorgabe im Wortlaut

> Erstelle ein ruhiges, atmosphärisches, hochwertiges Bild im 16:9-Format für KE!N EINZELFALL e. V.
>
> Thema: [Thema einsetzen]
> Konkrete Bildidee: [Motiv einsetzen]
>
> Warme Sand-, Beige-, Creme- und Naturtöne, weiches goldenes Sonnenlicht, sanfte Schatten und natürliche Reflexe. Ein weicher Lichtstrahl fällt aus der linken oberen Ecke schräg ins Bild und sorgt für eine warme, hoffnungsvolle, ruhige Atmosphäre.
>
> Die Bildsprache soll ruhig, erwachsen, seriös, emotional und hochwertig wirken – minimalistisch, natürlich und nicht gestellt.
>
> Das eigentliche Motiv befindet sich am Rand, bevorzugt links unten. Der obere Bereich und die rechte Bildhälfte bleiben weitgehend frei. Insgesamt sollen etwa 40–60 %, bei Reels gern 70–75 % freie Fläche entstehen, damit später in Canva Text eingefügt werden kann.
>
> Keinen Text ins Bild einfügen. Keine Überschriften, keine Schrift, kein Logo, keine grafischen Elemente.
>
> Natürliche Materialien wie Holz, Leinen, Papier oder Keramik dürfen dezent eingesetzt werden.
>
> Keine grellen Farben, keine bunte Flyer-Optik, keine überladenen Symbole, keine hektische Szene, keine dramatischen oder traurigen Gesichter und keine direkte Gewaltdarstellung. Emotional, aber nicht kitschig, reißerisch oder belehrend.
>
> Das Bild soll wirken, als gehöre es zu einem einheitlichen professionellen Fotoshooting mit allen anderen Bildern von KE!N EINZELFALL.

## Wie wir sie umsetzen

- **Modell:** fal.ai, `flux-pro/v1.1-ultra`, 16:9.
- **Englisch statt Deutsch:** Mit dem deutschen Wortlaut hielt sich das Modell
  kaum an die Anordnung (Motiv mittig, Hände mit Fehlern). Die englische
  Übersetzung in [`bildvorlage-en.txt`](bildvorlage-en.txt) ist inhaltlich
  dieselbe Vorgabe, verlangt die Anordnung nur ausdrücklicher („nothing stands
  in the right half“) und legt Kamera und Licht fest, damit die Reihe
  zusammenpasst.
- **Stillleben statt Menschen:** Keine Gesichter, keine Hände. Das hält die
  Reihe ruhig und vermeidet die typischen KI-Fehler an Händen.
- **Erzeugen:** `FAL_KEY=… bin/titelbild <slug> "<Thema>" "<Motiv>"` legt
  `public/img/titelbilder/<slug>.webp` an. Danach ansehen: Steht das Motiv
  links? Ist irgendwo Schrift im Bild (Kalender, Bücher)? Dann neu erzeugen.

## Wo Titelbilder stehen

Als Band über die volle Breite im Seitenkopf. Darauf steht ab Tablet-Breite
in der rechten Hälfte immer dasselbe: grüne Zeile (Bereich, sonst der
Vereinsname), Titel, eine kurze Unterzeile. Ein heller Schleier von rechts
hält den Text lesbar. Die Brotkrumen stehen unter dem Bild, der erste Absatz
der Seite bleibt im Inhalt. Seit KEV-36 ist das Bild auf jeder Breite der
Hintergrund, der Kopf etwa halb so hoch wie das Fenster. Auf dem Handy steht
der Text oben auf der freien Wand, mit hellem Schleier von oben. Bild und Unterzeile werden je Seite im Panel unter „Titelbild“
gepflegt; welche Seiten eins haben, steht mit den Unterzeilen in
`App\Support\Titelbilder`. /veranstaltungen ist eine eigene Übersicht, nimmt
aber das Bild der gleichnamigen Seite.

**Daraus folgt für jedes neue Bild:** In der rechten Hälfte darf nichts
stehen, sonst liegt es unter dem Titel. Im Motiv deshalb ausdrücklich „only
in the left third of the frame“ verlangen und das Ergebnis in der Seite
ansehen, bei 1024, 1440 und 1920 px. Jedes Bild liegt zweimal vor:
`<slug>.webp` (2000 px) und `<slug>-1000.webp` für kleinere Bildschirme,
`bin/titelbild` legt beide an.

| Seite | Motiv |
|---|---|
| Verein | Vereinslogo auf der Wand über leerem Tisch (siehe unten) |
| Über uns – Vorstand und Team | Vereinslogo auf leerer Wand mit Fensterlicht, wie Verein (KEV-62, statt zwei Keramikschalen) |
| Startseite (Aufmacher) | leere Wand mit goldenen Lichtstrahlen und Blätterschatten, ohne Motiv; davor das Logo. Nach KEV-29 kurz ohne Blätter, auf Wunsch des Vereins wieder mit (28.09.2026) |
| Mitgliedschaft | Mitgliedsantrag mit Stift, von Taddi geliefert (KEV-61); Kleingedrucktes weich gezeichnet, oben ausgerichtet (siehe unten) |
| Spenden | Keramikschale mit Samen, Gräser, Leinen |
| Unterstützung | Keimling im Terrakottatopf |
| Selbsthilfegruppen | drei Holzstühle nebeneinander in hellem Raum |
| Selbsthilfegruppe „Wir sind nicht mehr stumm“ | rissige Wand im Streiflicht, Risse links, rechts glatt; von Taddi geliefert (KEV-82). Gruppen tragen ihr Bild im Feld „Titelbild“ der Gruppe, Datei `gruppe-<slug>.webp` |
| Istanbul-Konvention, Kinderkodex | ein Bild für beide: Ordner mit Registern, darauf das Vereinslogo; von Taddi geliefert (KEV-79, statt Platzhalter). Datei `ordner-mit-logo.webp`, ausgerichtet auf 42 %, auf dem Handy Text unten (`Titelbilder::TEXT_UNTEN`) |
| Gremium UKFB (Entwurf) | runder Holztisch mit sechs Polsterstühlen; von Taddi geliefert (KEV-81), ausgerichtet auf 70 % |
| Beschwerdemanagement | Briefschlitz mit Umschlag an heller Putzwand; von Taddi geliefert (KEV-80), ausgerichtet auf 35 % |
| Arbeitsgruppen | Holztisch mit Mappe, Karten und zwei Stiften, Stühle; von Taddi geliefert (KEV-83, statt Papier, Vase, Leinen), mittig ausgerichtet (`Titelbilder::FOKUS`, 50 %) |
| Anfragen | Briefumschlag auf Holztisch |
| Kontakt | zwei Becher nebeneinander am langen Tisch |
| Wissen | Bücherstapel mit Leinenband |
| Publikationen | Broschürenstapel und Lesebrille |
| Veranstaltungen | Becher, Vase mit Trockenblumen, Tisch |
| Projekte | Holzhaus-Modell, Papier, Bleistift |
| KE!N EINZELFALL im Dialog | Stuhl mit Beistelltisch an der Wand |
| Soziales Entschädigungsrecht | Papierstapel mit Füller |
| Traumafolgestörungen verstehen | mit Gold reparierte Schale (Kintsugi) |
| Trauma, Bindung und Beziehung | zwei Steine, aneinandergelehnt |

Bewusst ohne Bild: Rechtstexte, Barrierefreiheit, Trigger-Warnung und die
Themenseiten unter „Wissen“ (GdB, Pflegegrad, …). Dort geht es nur um den
Text, und zwanzig fast gleiche Schreibtischbilder machten die Reihe beliebig.

Die Bilder sind KI-erzeugt und zeigen nichts Echtes aus dem Verein. Der
Verein sollte sie einmal ansehen und freigeben.

## Ausnahme: Verein mit Logo (KEV-57)

Taddi, 27.09.2026: Auf der Vereinsseite keine Kaffeetassen und nichts, was
nach Wellness aussieht, sondern das Logo des Vereins. Das widerspricht
bewusst dem Satz „kein Logo“ aus der Vorgabe, der Wunsch kommt vom Verein
selbst.

Das Logo malt nicht die KI (sie verhunzt die Schrift). Stattdessen:

1. Mit `bin/titelbild` eine leere Szene erzeugt: nur Wand, Tischkante, Licht,
   „no objects at all“. Von drei Versuchen war nur einer wirklich leer.
2. Das echte Logo (`Logo.jpg`, 486 × 434, von der Altseite unter
   `wp-content/uploads/2024/09/`) mit Pillow multiplizierend auf die Wand
   gelegt, 92 % Deckkraft. So verschwindet der weiße Grund, und Licht und
   Schatten der Wand liegen über dem Logo, als wäre es aufgemalt.
3. Größe und Lage: 25 % der Bildhöhe, 9 % vom linken Rand, Unterkante knapp
   über der Tischkante (79 % der Höhe). Höher darf es nicht stehen: Bei
   1920 px zeigt das Band nur die untere Bildhälfte.

## Platzhalter (KEV-36)

Seit das Titelbild im Seitenkopf der Hintergrund ist, soll jede Seite eins
haben. Wo noch kein eigenes existiert (Rechtstexte, Barrierefreiheit,
Themenseiten unter „Wissen“), steht vorerst `platzhalter.webp`: leere warme
Wand mit Fensterlicht, ohne Motiv. Liste in
`App\Support\Titelbilder::PLATZHALTER_SEITEN`. Die richtigen Bilder kommen in
späteren Tickets; wer eins setzt, ersetzt einfach den Platzhalter im Panel.

Erzeugt direkt über fal.ai, **nicht** mit `bin/titelbild`: Die Vorlage
verlangt ein Motiv links unten, und das Modell stellte dann trotz Verbot
Tassen, Vasen oder Pflanzen hin. Tassen und „Wellness“ hat der Verein
ausdrücklich abgelehnt (KEV-57, KEV-36).

## Mitgliedschaft: Bild vom Verein (KEV-61)

Taddi hat das Bild selbst geliefert: ein Mitgliedsantrag mit Stift. Es ist
KI-erzeugt, und das Kleingedruckte stimmte nicht: falsche IBAN
(„DE90 … 8589 10“ statt der echten), „Schlimmer Höhe“ statt „Schiffbeker
Höhe“. Beides ist beim Hineinzoomen lesbar, und eine falsche IBAN auf der
Mitgliedschaftsseite wäre ein echter Schaden. Deshalb sind der Fuß des
Formulars (Kontakt, Bank, Steuernummer) und die Adresse oben weich
gezeichnet; Überschrift und Formular bleiben scharf. Die Beiträge im
Formular (12/24/36 €) hat der Verein gegenzuprüfen.

Das Motiv reicht bis an den oberen Rand, anders als die Vorgabe es vorsieht.
Es steht deshalb in `Titelbilder::FOKUS` (0 %) und wird im Kopf oben statt
unten ausgerichtet.

**Ausrichtung allgemein:** Der Seitenkopf schneidet je nach Breite oben oder
unten ab. Ohne Eintrag richtet er unten aus, wie die Bildvorgabe es vorsieht.
Taddis eigene Bilder halten sich nicht immer daran. Für sie steht in
`Titelbilder::FOKUS`, auf welcher Höhe (in Prozent von oben) das Motiv sitzt.
Prüfen bei 1440 und 1920 px: Dort fällt am meisten weg.

**Motiv oben, Handy (KEV-79):** Auf dem Handy steht der Text oben auf dem
Bild, wo laut Vorgabe freie Wand ist. Sitzt das Motiv in der oberen Hälfte
(das Logo auf dem Ordner), läge der Text darauf. Solche Bilder stehen in
`Titelbilder::TEXT_UNTEN`: Auf dem Handy steht der Text dann unten, mit einem
kürzeren Schleier von unten, und der Kopf ist 60 statt 50 % der Fensterhöhe
hoch. Geprüft von 320 × 568 bis 767 × 900; ab „md“ steht der Text wie immer
rechts. Teilen sich Seiten ein Bild, steht der Dateiname in
`Titelbilder::DATEI`.

