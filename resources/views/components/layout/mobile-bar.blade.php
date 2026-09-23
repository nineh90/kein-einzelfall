{{--
    Mobile Sticky-Bar.

    In akuten Situationen die primäre Navigation. Deshalb: feste Position statt
    sticky (darf nicht wegscrollen), großzügige Trefferflächen, und der Notausgang
    immer an derselben Stelle — die Position ist Teil der Verlässlichkeit.

    pb-[env(safe-area-inset-bottom)] hält die Bar über der Home-Indicator-Leiste
    auf iPhones, sonst liegt der Exit-Button unter dem Systembalken.
--}}
<nav aria-label="{{ __('rahmen.schnellzugriff') }}"
     class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-card pb-[env(safe-area-inset-bottom)] lg:hidden">
    <ul class="flex items-stretch justify-around">
        @foreach (\App\Support\Navigation::mobilLeiste() as $item)
            <li class="flex-1">
                <a href="{{ $item['url'] }}"
                   @if (request()->is(ltrim($item['url'], '/') ?: '/')) aria-current="page" @endif
                   class="flex min-h-14 flex-col items-center justify-center gap-1 px-2 py-2
                          text-[0.6875rem] no-underline text-ink-soft
                          aria-[current=page]:text-green">
                    <x-ui.icon :name="$item['icon']" :size="22" />
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach

        {{-- Darstellung und Barrierefreiheit (KEV-26). Vorher ein Tab am
             linken Rand, das auf dem Handy über dem Text lag. Ein Knopf, kein
             Link: Er öffnet das Panel aus x-layout.a11y-toolbar. Ohne
             JavaScript ist das Panel nicht bedienbar, der Knopf deshalb
             erst mit Skript sichtbar (a11y.js nimmt das hidden weg).
             Steht vor dem Notausgang, damit der ganz rechts bleibt. --}}
        <li class="flex-1" data-a11y-leiste hidden>
            <button type="button"
                    data-a11y-oeffnen
                    aria-expanded="false"
                    aria-controls="a11y-panel"
                    class="relative flex min-h-14 w-full flex-col items-center justify-center gap-1 px-2 py-2
                           text-[0.6875rem] text-ink-soft aria-expanded:text-green">
                <span class="relative">
                    <x-ui.icon name="accessibility" :size="22" />
                    <span data-a11y-zaehler hidden
                          class="absolute -right-2 -top-1.5 flex h-4 min-w-4 items-center justify-center
                                 rounded-full bg-green px-1 text-[0.625rem] text-on-green"></span>
                </span>
                <span aria-hidden="true">{{ __('rahmen.darstellung_leiste') }}</span>
                <span class="sr-only">Darstellung und Barrierefreiheit einstellen</span>
            </button>
        </li>

        <li class="flex-1">
            <span class="flex min-h-14 items-center justify-center">
                <x-layout.exit-button variant="bar" />
            </span>
        </li>
    </ul>
</nav>

{{-- Den Ausgleich für die Leiste trägt seit KEV-26 die Fusszeile als
     Innenabstand. Ein eigener Streifen darunter stand als heller Balken
     unter dem grünen Fuss. --}}
