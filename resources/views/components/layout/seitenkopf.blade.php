@props([
    'titel',
    'bereich' => null,
    'krumen' => [],
    'lead' => null,
    // Fläche des ersten Abschnitts darunter: Der Kopf steht auf ihr.
    'auf' => 'cream',      // cream | card
    'bild' => null,        // Titelbild, 16:9
    'bild_alt' => null,
    // Kurzer Satz unter dem Titel, nur auf dem Titelbild (dort statt des Vorspanns).
    'untertitel' => null,
])

{{--
    Seitenkopf.

    Ohne ihn beginnt jede Unterseite mit einer nackten Überschrift auf leerer
    Fläche — die Startseite hat einen Aufmacher, die Unterseiten wirkten dagegen
    unfertig. Der Kopf gibt ihnen denselben ruhigen Einstieg, ohne dass dafür
    Inhalte erfunden werden müssten: Bereich und Brotkrumen stammen aus der
    Navigation, der Vorspann ist der erste Absatz der Seite.
--}}
{{--
    Seit dem 23.09.2026 schlank und ohne eigenes Band (Abnahme): Vorher war
    der Kopf eine eigene Karte mit Linie darunter, und das Größte darin war
    ein einzelnes Wort wie „Spenden“ in 40 px. Jetzt stehen Brotkrumen und
    Titel auf der Fläche des ersten Abschnitts, ohne Trennlinie, und der
    Inhalt beginnt direkt darunter.

    Die H1 bleibt: Vorlesehilfen springen auf sie, um zu erfahren, wo man ist,
    und sie ist das Thema der Seite für Suchmaschinen. Sie ist nur kleiner.

    data-anschliessend: Der Kopf gehört zum Abschnitt darunter und ist kein
    eigener Abschnitt im Flächenwechsel (SeitengestaltungTest).
--}}
{{--
    Titelbild (seit 24.09.2026).

    Die Bilder sind nach der Vorgabe des Vereins für Text gebaut: Motiv links
    unten, oben und rechts freie Wand (docs/Bildsprache.md). Deshalb steht
    das Bild als Band über die volle Breite, und der Text steht ab „md“ in der
    rechten Hälfte auf der freien Fläche. Ein heller Schleier von rechts hält
    ihn lesbar, auch wo Sonnenflecken auf der Wand liegen.

    Auf dem Bild steht auf jeder Seite dasselbe, in derselben Reihenfolge:
    grüne Zeile, Titel, Unterzeile. Vorher war es je nach Seite nur der Titel,
    Titel mit Bereich oder Titel mit einem ganzen Absatz, und nebeneinander
    wirkten die Köpfe zusammengewürfelt. Deshalb:
      - Die grüne Zeile ist der Bereich. Hat die Seite keinen, oder heißt er
        wie die Seite selbst („Kontakt“ über „Kontakt“), steht dort der
        Vereinsname.
      - Statt des Vorspanns steht die kurze Unterzeile der Seite. Der erste
        Absatz bleibt im Inhalt (page.blade.php).
      - Die Brotkrumen stehen nicht auf dem Bild, sondern darunter. Links
        auf einem Stimmungsbild lenken ab und sind auf der Wand schlecht zu
        treffen.

    Ausgerichtet wird auf die linke untere Ecke (object-position), dort sitzt
    das Motiv. Was bei flachen Bildschirmen wegfällt, ist leere Wand. Die
    Höhe wächst mit der Breite (clamp), sonst schnitte ein 1920 px breiter
    Bildschirm das 16:9-Bild auf ein Viertel zu, und das Motiv wirkte riesig.

    Seit KEV-36 auch auf dem Handy: Bild als Hintergrund, Text oben darauf
    (dort ist laut Bildvorgabe freie Wand), halbe Fensterhöhe.

    Ohne alt-Text ist es Schmuck (alt=""), Vorlesehilfen überspringen es.
--}}
@if ($bild)
    @php
        // bild.webp (2000 px) und bild-1000.webp; ein fremder Pfad aus dem
        // Panel hat keine kleine Fassung und kommt ohne srcset aus.
        $klein = preg_replace('/\.webp$/', '-1000.webp', $bild);
        $srcset = $klein !== $bild && is_file(public_path(ltrim($klein, '/')))
            ? "{$klein} 1000w, {$bild} 2000w"
            : null;

        $zeile = $bereich && $bereich !== $titel ? $bereich : 'KE!N EINZELFALL e.V.';

        // Ausgerichtet wird links unten, dort sitzt laut Bildvorgabe das
        // Motiv. Ausnahmen mit ihrer Höhe stehen in Titelbilder::FOKUS.
        // Als style, nicht als Klasse: Tailwind kennt nur Werte, die
        // wörtlich in den Vorlagen stehen.
        $fokus = \App\Support\Titelbilder::fokus($bild);

        // Auf dem Handy steht der Text sonst oben, auf der freien Wand. Bei
        // Bildern mit dem Motiv oben läge er darüber (KEV-79: das Logo auf
        // dem Ordner). Dort steht er unten, mit dem Schleier von unten.
        $textUnten = \App\Support\Titelbilder::textUnten($bild);

        // Das echte Vereinslogo als eigenes Element über dem Bild (KEV-75).
        $logo = \App\Support\Titelbilder::logoDarauf($bild);
    @endphp

    <header data-anschliessend @class(['bg-card' => $auf === 'card'])>
        {{-- Seit KEV-36 auf jeder Breite ein Hintergrundbild mit dem Text
             darauf, etwa halbe Fensterhöhe (min-h, längere Titel dürfen
             wachsen). Vorher stand das Bild auf dem Handy als Band über dem
             Text. --}}
        {{-- Mit Text unten auf dem Handy etwas höher (60svh): Sonst rückte
             der Titel bis an die Unterkante des Motivs. --}}
        {{-- Mit aufgelegtem Logo auf dem Handy so hoch wie der Inhalt: Logo
             und Text stehen untereinander im Fluss, eine Mindesthöhe nach
             Fensterhöhe machte nur die Lücke dazwischen größer (Kevin,
             03.10.2026). --}}
        <div @class([
            'relative isolate flex overflow-hidden',
            'min-h-[50svh]' => ! $textUnten,
            'min-h-[60svh] md:min-h-[50svh]' => $textUnten && ! $logo,
            'md:min-h-[50svh]' => $logo,
        ])>
            <div class="absolute inset-0 -z-10">
                {{-- Nicht lazy: Das Bild steht im ersten Bildschirm. --}}
                <img src="{{ $bild }}" @if ($srcset) srcset="{{ $srcset }}" sizes="100vw" @endif
                     alt="{{ $bild_alt ?? '' }}" width="2000" height="1116" fetchpriority="high"
                     class="h-full w-full object-cover" style="object-position: {{ $fokus }}">

                {{-- Schleier für die Lesbarkeit. Auf dem Handy steht der Text
                     oben, wo die Bilder laut Vorgabe freie Wand haben; das
                     Motiv links unten bleibt frei. Ab „md“ wie bisher von rechts. --}}
                {{-- Mit Text unten kürzer: Er braucht nur das untere Drittel,
                     und das Motiv darüber soll nicht verblassen. --}}
                <div aria-hidden="true" @class([
                    'absolute inset-0 md:hidden',
                    'bg-linear-to-b from-cream/95 from-35% via-cream/70 via-55% to-transparent to-80%' => ! $textUnten,
                    'bg-linear-to-t from-cream/95 from-20% via-cream/70 via-30% to-transparent to-45%' => $textUnten,
                ])></div>
                <div aria-hidden="true"
                     class="absolute inset-0 hidden bg-linear-to-r from-transparent from-25% via-cream/70 via-50% to-cream/90 md:block"></div>
            </div>

            <div @class([
                'flex w-full px-4 pb-10 pt-6 md:items-center md:px-8 md:py-12 lg:px-10 lg:py-16',
                'items-end' => $textUnten && ! $logo,
            ])>
                {{-- Mit Logo: auf dem Handy Logo oben, darunter mit festem
                     Abstand der Text, ab „md“ nebeneinander, Logo mittig in
                     der linken Hälfte. --}}
                <div @class([
                    'mx-auto w-full max-w-6xl',
                    'flex flex-col gap-8 md:flex-row md:items-center' => $logo,
                ])>
                    @if ($logo)
                        {{-- Schmuck: Der Vereinsname steht in der grünen Zeile
                             und im Titel, deshalb alt="". --}}
                        <div class="flex md:w-1/2 md:justify-center">
                            <img src="/img/logo-gross.webp" alt="" width="479" height="432" fetchpriority="high"
                                 data-kopf-logo
                                 class="h-auto w-44 md:w-52 lg:w-64 xl:w-72">
                        </div>
                    @endif

                    {{-- Die Spalte wächst mit dem Titel (w-fit), von der halben
                         Breite (ab „lg“ 45 %) bis 60 bzw. 55 %. Fest 45 % brachen
                         „Gruppen & Veranstaltungen“, „Erwerbsminderungsrente“
                         und neun weitere Titel um, obwohl rechts Platz war
                         (Kevin, 03.10.2026, KEV-72). Kurze Titel stehen wie
                         bisher. Dazu zwischen 768 und 1279 px die Titelschrift
                         eine Stufe kleiner, sonst reicht es dort nicht. --}}
                    <div class="md:ml-auto md:w-fit md:min-w-1/2 md:max-w-[60%] lg:min-w-[45%] lg:max-w-[55%]">
                        <x-ui.eyebrow class="mb-3">{{ $zeile }}</x-ui.eyebrow>

                        {{-- hyphens-auto: Lange Fachbegriffe („Opferentschädigungsgesetz“)
                             brachen in der halben Spalte sonst ohne Trennstrich
                             mitten im Wort um. Die Seite trägt lang="de". --}}
                        <h1 class="text-balance hyphens-auto break-words font-display text-[1.75rem] font-medium leading-tight text-green md:text-[1.875rem] lg:text-[2.375rem] xl:text-[2.75rem]">
                            {{ $titel }}
                        </h1>

                        @if ($untertitel)
                            <p class="mt-4 max-w-md text-pretty text-lg leading-snug text-ink lg:text-xl">
                                {{ $untertitel }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if (count($krumen) > 1)
            <div class="px-4 pt-6 md:px-8 lg:px-10">
                <div class="mx-auto max-w-6xl [&>nav]:mb-0">
                    <x-ui.brotkrumen :krumen="$krumen" />
                </div>
            </div>
        @endif
    </header>
@else
    <header data-anschliessend @class([
        'px-4 md:px-8 pt-6 lg:px-10 lg:pt-10',
        'bg-card' => $auf === 'card',
    ])>
        <div class="mx-auto max-w-6xl">
            <x-ui.brotkrumen :krumen="$krumen" />

            @if ($bereich)
                <x-ui.eyebrow class="mb-2">{{ $bereich }}</x-ui.eyebrow>
            @endif

            {{-- Kleiner als früher (40 px), aber immer eine Stufe über den
                 Abschnittsüberschriften (24/30 px): Eine H1 in derselben Größe
                 wie die H2 darunter kehrt die Rangordnung optisch um. --}}
            <h1 class="max-w-3xl font-display text-[1.75rem] font-medium leading-tight text-green lg:text-[2.25rem]">
                {{ $titel }}
            </h1>

            @if ($lead)
                {{-- Erster Absatz größer gesetzt: gibt der Seite einen Einstieg und
                     hilft beim Einordnen, bevor der Fließtext beginnt. --}}
                <p class="mt-4 max-w-prose text-lg leading-relaxed text-ink-soft">
                    {{ hervorheben($lead) }}
                </p>
            @endif
        </div>
    </header>
@endif
