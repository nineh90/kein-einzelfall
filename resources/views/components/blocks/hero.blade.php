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
{{-- Füllt den ersten Bildschirm (KEV-36): Höhe des Fensters abzüglich
     Kopfzeile (4 rem, ab „lg“ 5 rem) und, unter „xl“, der festen Leiste unten
     (4 rem). svh statt vh, damit mobile Browserleisten nichts abschneiden.
     Nur Mindesthöhe: Längere Texte dürfen den Aufmacher wachsen lassen. --}}
@php
    // bild.webp (2000 px) und bild-1000.webp, wie bei den Titelbildern.
    $klein = $bild ? preg_replace('/\.webp$/', '-1000.webp', $bild) : null;
    $srcset = $bild && $klein !== $bild && is_file(public_path(ltrim($klein, '/')))
        ? "{$klein} 1000w, {$bild} 2000w"
        : null;
@endphp

{{-- Spalte statt Zeile: Inhalt mittig (my-auto), das Band „Sofort verlassen“
     am unteren Rand (KEV-30). --}}
<section class="relative isolate flex min-h-[calc(100svh-8rem)] flex-col overflow-hidden px-4 md:px-8 pb-6 pt-8 lg:min-h-[calc(100svh-9rem)] lg:px-10 lg:pb-8 lg:pt-16 xl:min-h-[calc(100svh-5rem)]">
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

    <div class="mx-auto my-auto grid w-full max-w-6xl items-center gap-6 md:grid-cols-[1.25fr_0.75fr] md:gap-8 lg:grid-cols-[1.15fr_0.85fr] lg:gap-10">

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
            <h1 class="text-balance pb-1 font-display text-[1.75rem] font-medium leading-[1.18] text-ink md:text-[2.125rem] lg:text-[2.75rem]">
                {!! $ueberschrift !!}
            </h1>

            @if ($text)
                <p class="mt-4 max-w-prose text-[0.9375rem] leading-relaxed text-ink-soft lg:text-lg">
                    {{ $text }}
                </p>
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
                     class="h-auto w-40 sm:w-56 lg:w-80">
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
