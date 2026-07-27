@php
    $nav = \App\Support\Navigation::haupt();

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
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-2 px-4 py-3 sm:gap-4 lg:px-10 lg:py-5">

        {{-- Wortmarke.

             Darf nur auf sehr schmalen Geräten schrumpfen: Bei 320 px
             Fensterbreite — dem Wert, ab dem WCAG 1.4.10 waagerechtes Scrollen
             verbietet — hat sie die Kopfzeile auf 340 px aufgezogen und
             Einstellungsknopf und Menü aus dem Bild geschoben.

             Ab „sm“ wieder shrink-0: Dort ist Platz, und ein abgeschnittener
             Vereinsname ist kein akzeptabler Dauerzustand. --}}
        <a href="{{ \App\Models\Language::aktuell()->pfad('/') }}"
           class="flex min-w-0 items-center gap-2 no-underline sm:min-w-fit sm:shrink-0 sm:gap-2.5">
            <img src="/img/logo.png" alt="" width="36" height="36" class="h-9 w-9 shrink-0 object-contain">
            <span class="truncate font-display text-[0.9375rem] font-medium tracking-[0.01em] text-ink sm:text-base">
                KE!N EINZELFALL e.V.
            </span>
        </a>

        {{-- Desktop-Navigation.
             Die Untermenüs öffnen per :hover UND :focus-within — dadurch sind sie
             ohne eine Zeile JavaScript per Tastatur bedienbar.

             Sichtbar erst ab „xl“, nicht ab „lg“. Gemessen bei 1024 px: die
             Reihe braucht 1115 px (deutsch) bzw. 1105 px (russisch). Vorher fiel
             das nicht auf, weil „Gruppen & Termine“ still umbrach und die
             Kopfzeile auf 133 px auszog — auf Russisch wurde daraus ein
             dreizeiliger Menüpunkt. Mit dem Sprachumschalter ist die Reihe
             dauerhaft voller, deshalb bekommt sie erst dort Platz, wo sie
             wirklich passt. Zwischen 1024 und 1280 greift das Burger-Menü,
             das ohnehin vollständig bedienbar ist. --}}
        <nav aria-label="{{ __('rahmen.hauptnavigation') }}" class="hidden xl:block">
            <ul class="flex items-center gap-6">
                @foreach ($nav as $item)
                    <li class="group relative">
                        <a href="{{ $item['url'] }}"
                           @if ($istAktiv($item)) aria-current="page" @endif
                           class="flex items-center gap-1 whitespace-nowrap py-2 text-[0.9375rem] no-underline
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
            {{-- Steht vor dem Notausgang: Der Notausgang behält seine Position,
                 und die ist Teil seiner Verlässlichkeit. --}}
            <x-layout.sprachumschalter :fassungen="$fassungen ?? []" />

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
            <details class="xl:hidden">
                <summary
                    class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full
                           border border-line bg-card text-ink
                           [&::-webkit-details-marker]:hidden">
                    <span class="sr-only">{{ __('rahmen.menue') }}</span>
                    <x-ui.icon name="menu" :size="20" />
                </summary>

                <nav aria-label="{{ __('rahmen.hauptnavigation_mobil') }}"
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

                    {{-- Unterhalb „sm“ ist in der Kopfzeile kein Platz für beides.
                         Notausgang und Sprachwahl wandern deshalb hierher — der
                         Notausgang zuerst, seine Position ist Teil seiner
                         Verlässlichkeit. --}}
                    <div class="mt-3 flex flex-col gap-3 border-t border-line pt-3 sm:hidden">
                        <x-layout.exit-button />
                        <x-layout.sprachumschalter :fassungen="$fassungen ?? []" variant="menue" />
                    </div>
                </nav>
            </details>
        </div>
    </div>
</header>
