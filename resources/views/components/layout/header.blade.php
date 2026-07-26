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

            {{-- Burger nur mobil --}}
            <button type="button"
                    x-data
                    @click="$store.menu.toggle()"
                    :aria-expanded="$store.menu.offen ? 'true' : 'false'"
                    aria-controls="mobile-nav"
                    aria-expanded="false"
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-line
                           bg-card text-ink lg:hidden">
                <span class="sr-only">Menü öffnen</span>
                <x-ui.icon name="menu" :size="20" />
            </button>
        </div>
    </div>

    {{-- Mobile-Navigation.
         Ohne JavaScript bleibt Alpine stumm und das Panel wäre unerreichbar — deshalb
         blendet das <noscript> unten es dauerhaft ein. Lieber eine lange Seite als
         eine Seite ohne Navigation (der Fehler, den die Altseite macht). --}}
    <div id="mobile-nav"
         x-data
         x-show="$store.menu.offen"
         x-cloak
         @keydown.escape.window="$store.menu.offen = false"
         class="border-t border-line bg-card lg:hidden">
        <nav aria-label="Hauptnavigation (mobil)" class="max-h-[70vh] overflow-y-auto px-4 py-3">
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
    </div>

    <noscript>
        <style>
            #mobile-nav { display: block !important; }
        </style>
    </noscript>
</header>
