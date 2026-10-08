@props([
    'eyebrow' => null,
    'titel',
    'text' => null,
    'hand' => null,          // handschriftlicher Akzent unter der Grafik
    'bild' => null,          // Hintergrundbild (KEV-35), das Logo steht immer rechts
    'ctas' => [],            // [['label'=>, 'url'=>, 'variant'=>], ...]
])

@php
    // Leere Knöpfe aus dem Panel aussortieren, siehe helpers.php
    $knoepfe = knoepfe($ctas);

    /*
     * Der handschriftliche Teil der Überschrift aus dem Mockup, in Grün. Die
     * handgezeichnete Linie darunter ist seit KEV-43 weg (Wunsch des Vereins).
     *
     * Der Verein markiert im Panel den Teil der Überschrift, der ihn bekommen
     * soll — mit *Sternchen*, wie beim Fettschreiben in einer Nachricht. Das ist
     * bewusst kein zweites Titelfeld: Der Akzent kann so am Anfang, in der Mitte
     * oder am Ende des Satzes sitzen, ohne dass jemand die Reihenfolge zweier
     * Felder im Kopf zusammensetzen muss.
     *
     * Erst maskieren, dann ersetzen: `e()` lässt Sternchen unangetastet, und die
     * einzige rohe Ausgabe danach ist unser eigenes Markup — ein Titel mit
     * <script> darin bleibt damit harmlos.
     *
     * Die Schrift steht in der CSS (`.swash` in app.css).
     *
     * Steht vor dem markierten Teil noch Text, beginnt er als eigener Absatz
     * (KEV-24, mit Abstand seit KEV-43). Direkt hinter „müssen:“ hing das
     * Zitat mal am Zeilenende, mal halb in der nächsten Zeile, je nach
     * Bildschirmbreite. So steht es immer für sich. Der Leerraum davor fällt
     * dabei weg, sonst stünde am Ende der ersten Zeile ein unsichtbares
     * Leerzeichen. Ein <span> mit display:block statt eines <br>, damit sich
     * der Abstand setzen lässt; vorgelesen wird es wie bisher als ein Satz.
     */
    $ueberschrift = preg_replace_callback(
        '/(\s*)\*([^*]+)\*/u',
        fn (array $treffer) => $treffer[0][1] > 0
            ? '<span class="swash swash-absatz">'.$treffer[2][0].'</span>'
            : $treffer[1][0].'<span class="swash">'.$treffer[2][0].'</span>',
        e($titel),
        flags: PREG_OFFSET_CAPTURE,
    );
@endphp

{{-- Unten derselbe Abstand wie bei jedem Abschnitt: Der nächste Baustein
     steht auf der Karte, und ohne Luft klebte die Karte an den Knöpfen. --}}
{{-- Füllt den ersten Bildschirm (KEV-36, KEV-29): Höhe des Fensters abzüglich
     Kopfzeile und, unter „xl“, der festen Leiste unten (4 rem). Die Kopfzeile
     misst kopfhoehe.js und legt sie in --kopfhoehe ab; ohne JavaScript gelten
     4 bzw. 5 rem. svh statt vh, damit mobile Browserleisten nichts
     abschneiden. Nur Mindesthöhe: Längere Texte dürfen ihn wachsen lassen. --}}
@php
    // bild.webp (2000 px) und bild-1000.webp, wie bei den Titelbildern.
    $klein = $bild ? preg_replace('/\.webp$/', '-1000.webp', $bild) : null;
    $srcset = $bild && $klein !== $bild && is_file(public_path(ltrim($klein, '/')))
        ? "{$klein} 1000w, {$bild} 2000w"
        : null;
@endphp

{{-- Spalte statt Zeile: Inhalt mittig (my-auto), das Band „Sofort verlassen“
     am unteren Rand (KEV-30). --}}
<section class="relative isolate flex min-h-[calc(100svh-var(--kopfhoehe,4rem)-4rem)] flex-col overflow-hidden px-4 md:px-8 pb-6 pt-8 lg:min-h-[calc(100svh-var(--kopfhoehe,5rem)-4rem)] lg:px-10 lg:pb-8 lg:pt-12 xl:min-h-[calc(100svh-var(--kopfhoehe,5rem))]">
    {{-- Hintergrundbild (KEV-35): eine ruhige, leere Wand mit Fensterlicht,
         ohne Motiv, darauf links der Text und rechts das Logo. Bis zum
         28.09.2026 war es ein Steinstapel; der Verein will auf der Startseite
         aber das Logo statt der Steine (KEV-36, noch einmal bestätigt).
         Ein leichter Schleier von links hält den Text lesbar, auf dem Handy
         steht er über dem ganzen Bild. Schmuck: alt="". --}}
    @if ($bild)
        <div class="absolute inset-0 -z-10">
            <img src="{{ $bild }}" @if ($srcset) srcset="{{ $srcset }}" sizes="100vw" @endif
                 alt="" width="2000" height="1116" fetchpriority="high"
                 class="h-full w-full object-cover object-[0%_100%]">
            <div aria-hidden="true" class="absolute inset-0 bg-cream/60 md:hidden"></div>
            <div aria-hidden="true"
                 class="absolute inset-0 hidden bg-linear-to-r from-cream/80 from-10% via-cream/50 via-50% to-transparent md:block"></div>
        </div>
    @endif

    {{-- Ab „lg“ bekommt der Text mehr Breite als das Logo (KEV-29): Die
         Überschrift soll dort in zwei Zeilen stehen, „Keiner soll mehr sagen
         müssen:“ und das Zitat je für sich.

         Seit KEV-86 ist die Textspalte genau so breit wie ihr Inhalt (der
         Absatz endet bei 65 Zeichen), das Logo steht mittig im Rest, ohne
         Spaltenabstand. So ist links und rechts vom Logo gleich viel frei.
         Vorher war die Spalte breiter als der Text, und das Logo stand bündig
         am rechten Rand. --}}
    <div class="mx-auto my-auto grid w-full max-w-6xl items-center gap-6 md:grid-cols-[1.25fr_0.75fr] md:gap-8 lg:grid-cols-[minmax(0,max-content)_minmax(16rem,1fr)] lg:gap-x-0 lg:gap-y-10">

        {{-- Auf schmalen Viewports steht die Grafik oben (order-first), wie im Mockup.
             Zweispaltig schon ab „md“ (KEV-26): Auf dem Tablet stand der Stapel
             sonst allein über einer halbleeren Zeile. --}}
        <div class="order-2 md:order-1">
            @if ($eyebrow)
                <x-ui.eyebrow class="mb-4">{{ $eyebrow }}</x-ui.eyebrow>
            @endif

            {{-- pb-1: Die Unterlängen der Handschrift brauchen den Platz. --}}
            {{-- text-balance: Auf dem Handy blieb sonst „müssen:“ allein in
                 der zweiten Zeile stehen. --}}
            {{-- Ab „lg“ ohne Umbruch: Jeder der beiden Teile steht in einer
                 Zeile (KEV-29). Damit das auch bei 1024 px passt, wächst die
                 Schrift dort mit der Breite, bis 2,75 rem. --}}
            <h1 class="text-balance pb-1 font-display text-[1.75rem] font-medium leading-[1.18] text-ink md:text-[2.125rem] lg:whitespace-nowrap lg:text-[clamp(2.25rem,3.2vw,2.75rem)]">
                {!! $ueberschrift !!}
            </h1>

            {{-- Jede Zeile im Panel ein eigener Absatz (KEV-86, Wunsch des
                 Vereins: ein Satz je Zeile statt eines Blocks). --}}
            @if ($text)
                <div class="mt-4 flex max-w-prose flex-col gap-2 text-[0.9375rem] leading-relaxed text-ink-soft lg:text-lg">
                    @foreach (preg_split('/\R+/u', trim($text)) as $zeile)
                        <p>{{ $zeile }}</p>
                    @endforeach
                </div>
            @endif

            @if ($knoepfe)
                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    @foreach ($knoepfe as $cta)
                        <x-ui.button :href="$cta['url']" :variant="$cta['variant'] ?? 'primary'">
                            {{ $cta['label'] }}
                        </x-ui.button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="order-1 md:order-2">
            {{-- Das Vereinslogo statt des Steinstapels aus dem Mockup (KEV-36,
                 Wunsch des Vereins: keine Wellness-Steine). Freigestellt aus
                 dem Logo der Altseite, die weiße Schrift im grünen Band bleibt.
                 Schmuck neben der Überschrift, die den Namen ohnehin trägt:
                 deshalb alt="". Auf dem Handy kleiner, damit Überschrift und
                 Knöpfe im ersten Bildschirm bleiben. --}}
            <div class="hero-logo mx-auto flex w-full max-w-sm items-center justify-center">
                <img src="/img/logo-gross.webp" alt="" width="479" height="432" fetchpriority="high"
                     class="h-auto w-40 sm:w-56 lg:w-64 xl:w-80">
            </div>

            @if ($hand)
                <p class="mt-3 text-center font-hand text-2xl text-green lg:text-[1.6875rem]">
                    {{ $hand }}
                </p>
            @endif
        </div>
    </div>

    <x-layout.verlassen-band />
</section>
