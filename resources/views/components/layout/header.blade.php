@php
    $nav = config('navigation.main');

    // Aktiv = exakt diese URL oder eine ihrer Unterseiten.
    $istAktiv = function (array $item): bool {
        $urls = [$item['url'], ...array_column($item['children'] ?? [], 'url')];
        foreach ($urls as $url) {
            if (request()->is(ltrim($url, '/')) || (($url === '/') && request()->is('/'))) {
                return true;
            }
        }
        return false;
    };
@endphp

<header class="sticky top-0 z-40 border-b border-line bg-cream">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 lg:px-10 lg:py-5">

        {{-- Wortmarke --}}
        <a href="/" class="flex shrink-0 items-center gap-2.5 no-underline">
            <img src="/img/logo.png" alt="" width="36" height="36" class="h-9 w-9 object-contain">
            <span class="font-display text-base font-medium tracking-[0.01em] text-ink">
                KE!N EINZELFALL e.V.
            </span>
        </a>

        {{-- Desktop-Navigation.
             Die Untermenüs öffnen per :hover UND :focus-within — dadurch sind sie
             ohne eine Zeile JavaScript per Tastatur bedienbar. --}}
        <nav aria-label="Hauptnavigation" class="hidden lg:block">
            <ul class="flex items-center gap-6">
                @foreach ($nav as $item)
                    <li class="group relative">
                        <a href="{{ $item['url'] }}"
                           @if ($istAktiv($item)) aria-current="page" @endif
                           class="flex items-center gap-1 py-2 text-[0.9375rem] no-underline
                                  text-ink-soft hover:text-ink
                                  aria-[current=page]:font-medium aria-[current=page]:text-green">
                            {{ $item['label'] }}
                            @isset($item['children'])
                                <x-ui.icon name="chevron-down" :size="14"
                                           class="transition-transform group-hover:rotate-180" />
                            @endisset
                        </a>

                        @isset($item['children'])
                            <ul class="invisible absolute left-0 top-full z-50 min-w-64 rounded-card border
                                       border-line bg-card py-2 opacity-0 shadow-sm transition-[opacity,visibility]
                                       group-hover:visible group-hover:opacity-100
                                       group-focus-within:visible group-focus-within:opacity-100">
                                @foreach ($item['children'] as $child)
                                    <li>
                                        <a href="{{ $child['url'] }}"
                                           @if (request()->is(ltrim($child['url'], '/'))) aria-current="page" @endif
                                           class="block px-4 py-2 text-sm no-underline text-ink-soft
                                                  hover:bg-green-mist hover:text-ink
                                                  aria-[current=page]:font-medium aria-[current=page]:text-green">
                                            {{ $child['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endisset
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="flex shrink-0 items-center gap-2">
            <x-layout.a11y-toolbar />

            <div class="hidden sm:block">
                <x-layout.exit-button />
            </div>

            {{--
                Burger und Mobil-Navigation als natives <details>.

                Dasselbe Muster wie bei allen anderen Aufklappern im Projekt:
                ohne eine Zeile JavaScript bedienbar, per Tastatur bedienbar,
                und Screenreader melden auf/zu von selbst. Damit gibt es hier
                keinen Zustand mehr, der davon abhängt, ob ein Bundle geladen
                hat — die Navigation ist der letzte Ort, an dem man sich das
                leisten sollte (genau daran scheitert die Altseite).

                Das Panel selbst liegt absolut unter der Kopfzeile, damit es die
                ganze Breite bekommt und die Zeile darüber nicht auseinanderzieht.
                Als Bezug dient <header>: position:sticky zählt als positioniert.
            --}}
            <details class="lg:hidden">
                <summary
                    class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full
                           border border-line bg-card text-ink
                           [&::-webkit-details-marker]:hidden">
                    <span class="sr-only">Menü</span>
                    <x-ui.icon name="menu" :size="20" />
                </summary>

                <nav aria-label="Hauptnavigation (mobil)"
                     class="absolute inset-x-0 top-full max-h-[70vh] overflow-y-auto border-t border-line
                            bg-card px-4 py-3">
                    <ul class="flex flex-col gap-1">
                        @foreach ($nav as $item)
                            <li>
                                <a href="{{ $item['url'] }}"
                                   @if ($istAktiv($item)) aria-current="page" @endif
                                   class="block rounded-lg px-3 py-2.5 font-medium no-underline text-ink
                                          aria-[current=page]:bg-green-mist aria-[current=page]:text-green">
                                    {{ $item['label'] }}
                                </a>
                                @isset($item['children'])
                                    <ul class="mb-2 ml-3 border-l border-line pl-3">
                                        @foreach ($item['children'] as $child)
                                            <li>
                                                <a href="{{ $child['url'] }}"
                                                   class="block py-2 text-sm no-underline text-ink-soft">
                                                    {{ $child['label'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endisset
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-3 border-t border-line pt-3 sm:hidden">
                        <x-layout.exit-button />
                    </div>
                </nav>
            </details>
        </div>
    </div>
</header>
