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
der Seite bleibt im Inhalt. Auf dem Handy steht das Bild als flaches Band über
dem Text. Bild und Unterzeile werden je Seite im Panel unter „Titelbild“
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
| Verein | Teetasse auf gefalteter Leinenserviette |
| Über uns – Vorstand und Team | zwei Keramikschalen nebeneinander |
| Mitgliedschaft | Steinstapel (greift den Stapel der Startseite auf) |
| Spenden | Keramikschale mit Samen, Gräser, Leinen |
| Unterstützung | Keimling im Terrakottatopf |
| Selbsthilfegruppen | drei Holzstühle nebeneinander in hellem Raum |
| Arbeitsgruppen | Papier und Stifte, Vase, Leinen |
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
