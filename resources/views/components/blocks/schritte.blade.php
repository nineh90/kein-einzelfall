@props([
    'titel' => null,
    'einleitung' => null,
    'schritte' => [],     // [['titel'=>, 'text'=>], ...]
    'auf' => 'cream',
])

{{--
    Nummerierte Abfolge.

    Für die Kernthemen dieser Seite gedacht: Wie stelle ich einen Antrag nach
    dem OEG? Was passiert nach der Ablehnung? Welche Schritte hat ein
    Widerspruch? Als Fliesstext gehen solche Abläufe unter — als sichtbare
    Abfolge kann man sie abarbeiten und weiss jederzeit, wo man steht.

    Als <ol> ausgezeichnet, damit Screenreader „1 von 5" ansagen. Die
    sichtbaren Ziffern sind deshalb aria-hidden — sonst käme die Nummer doppelt.
--}}
<section @class([
    'px-4 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div class="mx-auto max-w-6xl">
        <div class="max-w-prose">
            @if ($titel)
                <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green"></span>
                <h2 class="mb-4 font-display text-2xl font-medium text-ink lg:text-3xl">{{ $titel }}</h2>
            @endif

            @if ($einleitung)
                <p class="mb-6 leading-relaxed text-ink-soft">{{ $einleitung }}</p>
            @endif

            <ol class="flex flex-col">
                @foreach ($schritte as $schritt)
                    <li class="relative flex gap-4 pb-6 last:pb-0">
                        {{-- Verbindungslinie zwischen den Punkten, beim letzten weggelassen --}}
                        @unless ($loop->last)
                            <span aria-hidden="true"
                                  class="absolute bottom-2 left-[1.0625rem] top-9 w-px bg-line"></span>
                        @endunless

                        <span aria-hidden="true"
                              class="relative z-10 flex h-[2.125rem] w-[2.125rem] shrink-0 items-center
                                     justify-center rounded-full border border-green bg-cream
                                     font-display text-base font-medium text-green-deep">
                            {{ $loop->iteration }}
                        </span>

                        <div class="flex-1 pt-1">
                            @if (!empty($schritt['titel']))
                                <p class="font-display text-lg font-medium text-ink">{{ $schritt['titel'] }}</p>
                            @endif
                            @if (!empty($schritt['text']))
                                <p class="mt-1 leading-relaxed text-ink-soft">{{ $schritt['text'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>
