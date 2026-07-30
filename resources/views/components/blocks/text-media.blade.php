@props([
    'titel' => null,
    'absaetze' => [],
    'bild' => null,
    'bild_alt' => null,
    'bild_seite' => 'rechts',   // rechts | links
    'cta' => null,
    'auf' => 'cream',
])

{{--
    Text mit Bild.

    Erwartet echtes Bildmaterial vom Verein. Bis das vorliegt, erscheint eine
    ruhige Platzhalterfläche statt eines leeren Kastens — so lässt sich die
    Seite schon zusammenstellen, ohne dass sie unfertig aussieht.

    Zum alt-Text: Trägt das Bild eine Aussage, gehört sie hier hinein. Ist es
    reine Dekoration, bleibt das Feld leer — dann überspringen Screenreader es,
    statt einen Dateinamen vorzulesen.
--}}
<section @class([
    'px-4 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div class="mx-auto grid max-w-6xl items-center gap-8 lg:grid-cols-2 lg:gap-12">

        <div @class(['order-2', 'lg:order-1' => $bild_seite === 'rechts', 'lg:order-2' => $bild_seite === 'links'])>
            @if ($titel)
                <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
                <h2 class="mb-4 font-display text-2xl font-medium text-ink lg:text-3xl">{{ $titel }}</h2>
            @endif

            <div class="flex flex-col gap-4 leading-relaxed text-ink-soft
                        [&>p:first-child]:text-[1.0625rem] [&>p:first-child]:text-ink">
                @forelse ($absaetze as $absatz)
                    <p>{{ $absatz }}</p>
                @empty
                    {{ $slot }}
                @endforelse
            </div>

            @if ($cta)
                <div class="mt-6">
                    <x-ui.button :href="$cta['url']" :variant="$cta['variant'] ?? 'primary'">
                        {{ $cta['label'] }}
                    </x-ui.button>
                </div>
            @endif
        </div>

        <div @class(['order-1', 'lg:order-2' => $bild_seite === 'rechts', 'lg:order-1' => $bild_seite === 'links'])>
            @if ($bild)
                <img src="{{ $bild }}" alt="{{ $bild_alt }}" loading="lazy"
                     class="w-full rounded-card border border-line object-cover">
            @else
                {{-- Platzhalter in der Farbwelt der Seite, nicht als grauer Kasten --}}
                <div aria-hidden="true"
                     class="flex aspect-[4/3] w-full items-end justify-start rounded-card border border-line
                            bg-[linear-gradient(135deg,#EFE4CC_0%,#D9C7A2_60%,#C3AE83_100%)] p-4">
                    <span class="rounded-full bg-cream/90 px-3 py-1 text-xs text-ink-soft">
                        Platzhalter — Foto folgt
                    </span>
                </div>
            @endif
        </div>
    </div>
</section>
