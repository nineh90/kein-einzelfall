@php
    $nav = \App\Support\Navigation::haupt();

    // Aktiv = exakt diese URL oder eine ihrer Unterseiten.
    $istAktiv = function (array $item): bool {
        $urls = [$item['url'], ...array_column($item['children'] ?? [], 'url')];
        foreach ($urls as $url) {
            // Unterseiten zählen mit: /selbsthilfegruppen/seelenfarben (KEV-73)
            // gehört zu „Gruppen & Veranstaltungen“, wie /veranstaltungen/….
            $pfad = ltrim($url, '/');
            if (request()->is($pfad) || ($pfad !== '' && request()->is($pfad.'/*')) || (($url === '/') && request()->is('/'))) {
                return true;
            }
        }
        return false;
    };
@endphp

<header class="sticky top-0 z-40 border-b border-line bg-cream">
    {{-- Ab „xl“ breiter als der Inhalt (max-w-7xl statt 6xl): Wortmarke,
         fünf Menüpunkte, Suche, Sprache und Notausgang brauchen 1190 px, der
         Inhalt hat 1072. Mit „Gruppen & Veranstaltungen“ (KEV-72) lief die
         Zeile bei 1280 px über den Fensterrand; schon vorher ragte sie 72 px
         in den Seitenrand. --}}
    {{-- data-kopf-zeile: kopfzeile.js misst, ob die Zeile passt, und schaltet
         sonst aufs Burger-Menü (größere Schrift, Prüfung der Firma 08.10.2026). --}}
    <div data-kopf-zeile class="mx-auto flex max-w-6xl items-center justify-between gap-2 px-4 md:px-8 py-3 sm:gap-4 lg:px-10 lg:py-5 xl:max-w-7xl">

        {{-- Wortmarke.

             Unter „md“ nur das Logo, etwas grösser (KEV-27). Ab „sm“ kommen
             Sprachwahl und die Beschriftung „Suche“ dazu, mit Name wurde die
             Zeile zwischen 640 und 767 px zu breit.
             Der Name passte dort neben Suche, Notausgang und Menü nie ganz
             hin: erst abgeschnitten, dann auf zwei Zeilen gestapelt. Der frei
             gewordene Platz geht an den Notausgang, der jetzt auch auf dem
             Handy beschriftet ist.

             Der Name bleibt für Vorlesehilfen im Link (sr-only), sonst hiesse
             der Link zur Startseite nur „Link, Bild“. --}}
        <a href="{{ \App\Models\Language::aktuell()->pfad('/') }}"
           class="flex shrink-0 items-center gap-2.5 no-underline">
            <img src="/img/logo.png" alt="" width="40" height="40" class="h-10 w-10 shrink-0 object-contain md:h-9 md:w-9">
            <span class="sr-only font-display text-base font-medium tracking-[0.01em] text-ink md:not-sr-only md:whitespace-nowrap">
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
        <nav data-kopf-menue aria-label="{{ __('rahmen.hauptnavigation') }}" class="hidden xl:block">
            <ul class="flex items-center gap-5">
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
            {{-- Suche (KEV-23).

                 Ein Link und kein aufklappbares Feld im Kopf: Ein Eingabefeld
                 zwischen Sprachumschalter und Notausgang wäre auf dem Handy
                 nicht unterzubringen, ohne einem von beiden Platz zu nehmen —
                 und der Notausgang behält seine Position, die ist Teil seiner
                 Verlässlichkeit. Der Link führt auf die Suchseite, wo das Feld
                 gross und mit sichtbarer Beschriftung steht.

                 Bleibt im Kopf und wandert nicht in die untere Leiste (KEV-27):
                 Die hat seit KEV-28 bewusst nur vier Einträge.

                 Beschriftung ab „sm" sichtbar, darunter nur die Lupe mit
                 sr-only-Text — ein Symbol allein sagt niemandem etwas, der es
                 nicht sieht. --}}
            @php
                $suche = \App\Models\Language::aktuell()->pfad('/suche');
                $aufSuche = request()->is(ltrim($suche, '/'));
            @endphp
            <a href="{{ $suche }}"
               @class([
                   'inline-flex items-center gap-2 rounded-full border border-line px-3 py-2',
                   'text-sm text-ink hover:bg-card',
                   'bg-card' => $aufSuche,
               ])
               @if ($aufSuche) aria-current="page" @endif>
                <x-ui.icon name="search" :size="18" />
                <span class="hidden sm:inline">{{ __('Suche') }}</span>
                <span class="sr-only sm:hidden">{{ __('Suche') }}</span>
            </a>

            {{-- Steht vor dem Notausgang: Der Notausgang behält seine Position,
                 und die ist Teil seiner Verlässlichkeit. --}}
            <x-layout.sprachumschalter :fassungen="$fassungen ?? []" />

            {{-- Die Darstellungs-/Barrierefreiheits-Einstellungen sind kein
                 Kopfzeilen-Knopf mehr, sondern ein fixes Tab am linken Rand
                 (siehe x-layout.a11y-toolbar, eingebunden im Layout gleich hinter
                 dem Sprunglink).

                 Der Notausgang steht jetzt auf JEDER Größe im klebenden Kopf —
                 vorher war er auf dem Handy nur unten in der Leiste und im Menü,
                 was ihn dort versteckte. Platz dafür ist da, seit der a11y-Knopf
                 aus der Reihe gewandert ist. Auf schmalen Geräten nur das Symbol,
                 ab 360 px mit Beschriftung — die Logik sitzt im Exit-Button. --}}
            <x-layout.exit-button />

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
            <details data-kopf-burger class="xl:hidden">
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
                    {{-- Bereiche als Akkordeon (03.10.2026, Wunsch des Vereins):
                         Alle Unterpunkte offen waren über 25 Zeilen, das Menü
                         wirkte riesig. Jetzt ist nur der Bereich offen, in dem
                         man gerade ist. Wieder natives <details>, ohne
                         JavaScript; `name` macht daraus ein echtes Akkordeon,
                         der Browser schliesst beim Öffnen den vorigen Bereich.
                         Ältere Browser ohne `name` lassen mehrere offen, mehr
                         passiert nicht.

                         Die Zeile des Bereichs klappt auf, sie führt nicht
                         mehr auf seine Seite. Deshalb steht die (/verein,
                         /wissen, …) als „Übersicht“ oben in der Liste, wenn sie
                         nicht ohnehin dort steht (Kontakt). --}}
                    <ul class="flex flex-col gap-1">
                        @foreach ($nav as $item)
                            <li>
                                @if (empty($item['children']))
                                    <a href="{{ $item['url'] }}"
                                       @if ($istAktiv($item)) aria-current="page" @endif
                                       class="block rounded-lg px-3 py-2.5 font-medium no-underline text-ink
                                              aria-[current=page]:bg-green-mist aria-[current=page]:text-green">
                                        {{ $item['label'] }}
                                    </a>
                                @else
                                    @php
                                        $aktiv = $istAktiv($item);
                                        $kinder = in_array($item['url'], array_column($item['children'], 'url'), true)
                                            ? $item['children']
                                            : [['label' => __('rahmen.uebersicht'), 'url' => $item['url']], ...$item['children']];
                                    @endphp
                                    <details name="mobilmenue" class="group/bereich" @if ($aktiv) open @endif>
                                        <summary @class([
                                            'flex cursor-pointer list-none items-center justify-between gap-3 rounded-lg px-3 py-2.5 font-medium [&::-webkit-details-marker]:hidden',
                                            'text-ink' => ! $aktiv,
                                            'bg-green-mist text-green' => $aktiv,
                                        ])>
                                            {{ $item['label'] }}
                                            <x-ui.icon name="chevron-down" :size="18"
                                                       class="shrink-0 transition-transform group-open/bereich:rotate-180" />
                                        </summary>
                                        <ul class="mb-2 ml-3 mt-1 border-l border-line pl-3">
                                            @foreach ($kinder as $child)
                                                @php $hier = request()->is(ltrim($child['url'], '/')); @endphp
                                                <li>
                                                    <a href="{{ $child['url'] }}"
                                                       @if ($hier) aria-current="page" @endif
                                                       class="block py-2 text-sm no-underline text-ink-soft
                                                              aria-[current=page]:font-medium aria-[current=page]:text-green">
                                                        {{ $child['label'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    {{-- Der Notausgang steht jetzt dauerhaft im Kopf (siehe oben)
                         und in der unteren Leiste — hier im Menü wäre er ein
                         dritter, versteckter Ort. Die Sprachwahl bleibt: In der
                         Kopfzeile ist sie erst ab „sm“ sichtbar, unterhalb also
                         nur hier. --}}
                    <div class="mt-3 border-t border-line pt-3 sm:hidden">
                        <x-layout.sprachumschalter :fassungen="$fassungen ?? []" variant="menue" />
                    </div>
                </nav>
            </details>
        </div>
    </div>
</header>
