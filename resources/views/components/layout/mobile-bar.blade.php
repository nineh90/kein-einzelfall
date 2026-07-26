{{--
    Mobile Sticky-Bar.

    In akuten Situationen die primäre Navigation. Deshalb: feste Position statt
    sticky (darf nicht wegscrollen), großzügige Trefferflächen, und der Notausgang
    immer an derselben Stelle — die Position ist Teil der Verlässlichkeit.

    pb-[env(safe-area-inset-bottom)] hält die Bar über der Home-Indicator-Leiste
    auf iPhones, sonst liegt der Exit-Button unter dem Systembalken.
--}}
<nav aria-label="Schnellzugriff"
     class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-card pb-[env(safe-area-inset-bottom)] lg:hidden">
    <ul class="flex items-stretch justify-around">
        @foreach (config('navigation.mobile_bar') as $item)
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

        <li class="flex-1">
            <span class="flex min-h-14 items-center justify-center">
                <x-layout.exit-button variant="bar" />
            </span>
        </li>
    </ul>
</nav>

{{-- Ausgleich, damit die Bar keinen Inhalt am Seitenende verdeckt. --}}
<div class="h-16 lg:hidden" aria-hidden="true"></div>
