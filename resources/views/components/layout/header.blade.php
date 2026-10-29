@php
    $nav = \App\Support\Navigation::haupt();

    // Aktiv = exakt diese URL oder eine ihrer Unterseiten.
    $istAktiv = function (array $item): bool {
        // Auch die Themenseiten unter einem Unterpunkt (KEV-238: /presse unter
        // „Öffentlichkeitsarbeit“).
        $urls = [$item['url'], ...array_column($item['children'] ?? [], 'url'),
            ...collect($item['children'] ?? [])->flatMap(fn ($kind) => array_column($kind['children'] ?? [], 'url'))->all()];
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
         sonst aufs Burger-Menü (größere Schrift, Prüfung der Firma 08.10.2026).
         Seit KEV-159 (sechster Bereich „Raum für Ungesagtes“) steht die
         Menüleiste ab „xl“ in einer zweiten Zeile: In einer Zeile bräuchte
         sie 1417 px, der Kopf hat höchstens 1280. Fest per CSS (xl:flex-wrap,
         xl:basis-full), nicht per Skript: Ohne JavaScript lief die Zeile
         sonst über, und bei 1280 px lag der Notausgang außerhalb des Fensters. --}}
    <div data-kopf-zeile class="mx-auto flex max-w-6xl items-center justify-between gap-2 px-4 md:px-8 py-3 sm:gap-4 lg:px-10 lg:py-5 xl:max-w-7xl xl:flex-wrap xl:gap-y-1">

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
            <img src="{{ bildadresse('/img/logo.png') }}" alt="" width="40" height="40" class="h-10 w-10 shrink-0 object-contain md:h-9 md:w-9">
            <span data-kopf-name class="sr-only font-display text-base font-medium tracking-[0.01em] text-ink md:not-sr-only md:whitespace-nowrap">
                KE!N EINZELFALL e.V.
            </span>
        </a>

        {{-- Desktop-Navigation als Mega-Menü (Entwurf, 28.10.2026).

             Vorher klappte je Bereich eine schmale Liste beim Drüberfahren
             auf, bei „Verein“ zehn Punkte untereinander. Jetzt öffnet ein
             Klick ein Feld über die ganze Breite: links Bild, Unterzeile und
             Übersicht des Bereichs, rechts die Unterpunkte in Gruppen
             (config/navigation.php, `gruppen`), Unterseiten darunter
             eingerückt (Klartext, Öffentlichkeitsarbeit).

             Per Klick statt beim Drüberfahren: Nichts klappt auf, nur weil die
             Maus vorbeikommt, und Tastatur und Touch-Laptop bedienen es gleich.
             Natives <details> wie im Handy-Menü, also ohne JavaScript bedienbar;
             `name` lässt immer nur einen Bereich offen. megamenue.js schliesst
             zusätzlich bei Klick daneben und bei Esc.

             Das Feld liegt absolut unter dem ganzen Kopf (Bezug: <header>,
             sticky zählt als positioniert), deshalb ist das <li> nicht relativ.

             Sichtbar erst ab „xl“, darunter das Burger-Menü (Gründe siehe
             Versionsgeschichte, die Reihe braucht über 1100 px). --}}
        @php $bereiche = \App\Support\Navigation::bereiche(); @endphp
        <nav data-kopf-menue aria-label="{{ __('rahmen.hauptnavigation') }}" class="hidden xl:order-3 xl:block xl:basis-full">
            <ul class="flex items-center gap-5">
                @foreach ($bereiche as $item)
                    @php $aktiv = $istAktiv($item); @endphp
                    <li>
                        @if (empty($item['children']))
                            <a href="{{ $item['url'] }}"
                               @if ($aktiv) aria-current="page" @endif
                               class="flex items-center gap-1 whitespace-nowrap py-2 text-[0.9375rem] no-underline
                                      text-ink-soft hover:text-ink
                                      aria-[current=page]:font-medium aria-[current=page]:text-green">
                                {{ $item['label'] }}
                            </a>
                        @else
                            @php
                                $bild = $item['bild'];
                                $klein = \App\Support\Titelbilder::klein($bild);
                                $uebersichtDrin = in_array($item['url'], array_column($item['children'], 'url'), true);
                            @endphp
                            <details name="megamenue" data-megamenue class="group/mega">
                                {{-- aria-current="true": Hier ist man gerade, aber der Bereich
                                     ist kein Link auf die Seite (die steht als „Übersicht“ im Feld). --}}
                                <summary @if ($aktiv) aria-current="true" @endif @class([
                                    'flex cursor-pointer list-none items-center gap-1 whitespace-nowrap py-2 text-[0.9375rem]',
                                    '[&::-webkit-details-marker]:hidden hover:text-ink group-open/mega:text-ink',
                                    'text-ink-soft' => ! $aktiv,
                                    'font-medium text-green' => $aktiv,
                                ])>
                                    {{ $item['label'] }}
                                    {{-- aria-current am <summary> gibt Chrome nicht an
                                         Vorlesehilfen weiter (geprüft im Barrierefreiheitsbaum),
                                         deshalb zusätzlich als Text. --}}
                                    @if ($aktiv)<span class="sr-only">, {{ __('aktueller Bereich') }}</span>@endif
                                    <x-ui.icon name="chevron-down" :size="14"
                                               class="transition-transform group-open/mega:rotate-180" />
                                </summary>

                                <div class="absolute inset-x-0 top-full z-50 border-y border-line bg-card shadow-[0_18px_40px_-24px_rgb(44_38_32/0.35)] motion-safe:animate-[menue-ein_.18s_ease-out]">
                                    <div @class(['mx-auto grid max-w-7xl gap-12 px-10 py-8', 'grid-cols-[17rem_1fr]' => ! $uebersichtDrin])>
                                        {{-- Der Bereich selbst: Bild, Unterzeile, Weg zur Übersicht.
                                             Nicht, wo die Übersicht selbst ein Unterpunkt ist (Kontakt),
                                             sonst stünde sie zweimal da. --}}
                                        @unless ($uebersichtDrin)
                                        <div>
                                            @if ($klein)
                                                <div class="relative">
                                                    <img src="{{ bildadresse($klein) }}" alt="" loading="lazy"
                                                         class="aspect-[16/10] w-full rounded-card border border-line object-cover"
                                                         style="object-position: {{ \App\Support\Titelbilder::fokus($bild) }}">
                                                    {{-- Wie im Seitenkopf: Beim Vereinsbild liegt das Logo obenauf (KEV-75). --}}
                                                    @if (\App\Support\Titelbilder::logoDarauf($bild))
                                                        <img src="{{ bildadresse('/img/logo-gross.webp') }}" alt="" loading="lazy"
                                                             class="absolute inset-0 m-auto h-3/5 w-auto">
                                                    @endif
                                                </div>
                                            @endif
                                            <p class="mt-4 font-display text-xl text-green">{{ $item['label'] }}</p>
                                            @if ($item['unterzeile'])
                                                <p class="mt-1 text-sm text-ink-soft">{{ $item['unterzeile'] }}</p>
                                            @endif
                                            <a href="{{ $item['url'] }}"
                                               @if (request()->is(ltrim($item['url'], '/'))) aria-current="page" @endif
                                               class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-green no-underline hover:underline">
                                                {{ __('rahmen.uebersicht') }}
                                                <x-ui.icon name="arrow-right" :size="16" />
                                            </a>
                                        </div>
                                        @endunless

                                        {{-- Ohne Gruppen (Raum für Ungesagtes, Projekte, Kontakt):
                                             die Unterpunkte als Kacheln mit Bild und Unterzeile. --}}
                                        @if (count($item['spalten']) === 1 && ! $item['spalten'][0]['titel'])
                                            <ul class="grid grid-cols-4 gap-5">
                                                @foreach ($item['spalten'][0]['eintraege'] as $child)
                                                    @php
                                                        $kb = $child['bild'];
                                                        $kk = \App\Support\Titelbilder::klein($kb);
                                                    @endphp
                                                    <li>
                                                        <a href="{{ $child['url'] }}"
                                                           @if (request()->is(ltrim($child['url'], '/'))) aria-current="page" @endif
                                                           class="group/kachel block rounded-card no-underline">
                                                            @if ($kk)
                                                                <img src="{{ bildadresse($kk) }}" alt="" loading="lazy"
                                                                     class="aspect-[16/10] w-full rounded-card border border-line object-cover transition-opacity group-hover/kachel:opacity-90"
                                                                     style="object-position: {{ \App\Support\Titelbilder::fokus($kb) }}">
                                                            @endif
                                                            <span class="mt-3 block text-[0.9375rem] font-medium text-ink group-hover/kachel:text-green group-aria-[current=page]/kachel:text-green">
                                                                {{ $child['label'] }}
                                                            </span>
                                                            @if ($child['unterzeile'])
                                                                <span class="mt-0.5 block text-sm text-ink-soft">{{ $child['unterzeile'] }}</span>
                                                            @endif
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                        {{-- Die Unterpunkte in Gruppen --}}
                                        <div class="grid auto-cols-fr grid-flow-col gap-8">
                                            @foreach ($item['spalten'] as $spalte)
                                                @php $gruppenId = 'mega-'.($item['schluessel'] ?? $loop->parent->index).'-'.$loop->index; @endphp
                                                <div>
                                                    {{-- Die Liste trägt den Gruppennamen als Namen: Vorlesehilfen
                                                         sagen „Liste, Über uns, 4 Einträge“. --}}
                                                    @if ($spalte['titel'])
                                                        <p id="{{ $gruppenId }}" class="border-b border-line pb-2 text-xs font-medium uppercase tracking-[0.12em] text-green">
                                                            {{ $spalte['titel'] }}
                                                        </p>
                                                    @endif
                                                    <ul @class(['mt-2' => $spalte['titel']]) @if ($spalte['titel']) aria-labelledby="{{ $gruppenId }}" @endif>
                                                        @foreach ($spalte['eintraege'] as $child)
                                                            <li>
                                                                <a href="{{ $child['url'] }}"
                                                                   @if (request()->is(ltrim($child['url'], '/'))) aria-current="page" @endif
                                                                   class="-mx-3 block rounded-lg px-3 py-2 text-[0.9375rem] no-underline text-ink
                                                                          hover:bg-green-mist
                                                                          aria-[current=page]:font-medium aria-[current=page]:text-green">
                                                                    {{ $child['label'] }}
                                                                </a>
                                                                @php $enkelkinder = array_filter($child['children'] ?? [], fn ($e) => empty($e['nicht_im_menue'])); @endphp
                                                                @if ($enkelkinder)
                                                                    <ul class="mb-1 ml-1 border-l border-line pl-3">
                                                                        @foreach ($enkelkinder as $enkel)
                                                                            <li>
                                                                                <a href="{{ $enkel['url'] }}"
                                                                                   @if (request()->is(ltrim($enkel['url'], '/'))) aria-current="page" @endif
                                                                                   class="block rounded-lg px-2 py-1 text-sm no-underline text-ink-soft
                                                                                          hover:bg-green-mist hover:text-ink
                                                                                          aria-[current=page]:font-medium aria-[current=page]:text-green">
                                                                                    {{ $enkel['label'] }}
                                                                                </a>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </details>
                        @endif
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
            <details data-kopf-burger class="group/burger xl:hidden">
                <summary
                    class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full
                           border border-line bg-card text-ink group-open/burger:border-green group-open/burger:text-green
                           [&::-webkit-details-marker]:hidden">
                    {{-- „Menü“ bleibt die Beschriftung, ob auf oder zu sagt
                         <details> selbst (erweitert / reduziert). --}}
                    <span class="sr-only">{{ __('rahmen.menue') }}</span>
                    <x-ui.icon name="menu" :size="20" class="group-open/burger:hidden" />
                    <x-ui.icon name="close" :size="20" class="hidden group-open/burger:block" />
                </summary>

                {{-- Handy und Tablet (Mega-Menü-Entwurf, 28.10.2026, Wunsch von
                     Taddi: „im Vergleich ziemlich langweilig“). Jeder Bereich
                     ist eine Karte mit Bild und Unterzeile, aufgeklappt
                     dieselben Gruppen wie am Desktop; Bereiche ohne Gruppen
                     zeigen ihre Seiten als Bildkacheln zum Wischen.

                     Wieder natives <details>, `name` lässt nur einen Bereich
                     offen (Akkordeon, Wunsch des Vereins vom 03.10.2026). Das
                     Feld reicht bis zum unteren Fensterrand und scrollt in
                     sich (overscroll-contain: am Ende scrollt nicht die Seite
                     dahinter weiter). Unten Luft für die Leiste mit Notausgang,
                     die auf dem Handy über allem liegt. --}}
                <nav aria-label="{{ __('rahmen.hauptnavigation_mobil') }}"
                     class="absolute inset-x-0 top-full max-h-[calc(100dvh-var(--kopfhoehe,4rem))] overflow-y-auto overscroll-contain
                            border-t border-line bg-card px-4 pb-24 pt-4 shadow-[0_18px_40px_-24px_rgb(44_38_32/0.35)]
                            motion-safe:animate-[menue-ein_.18s_ease-out]">
                    <ul class="mx-auto flex max-w-2xl flex-col gap-2.5">
                        @foreach ($bereiche as $item)
                            @php
                                $aktiv = $istAktiv($item);
                                $daumen = \App\Support\Titelbilder::klein($item['bild']);
                            @endphp
                            <li>
                                @if (empty($item['children']))
                                    <a href="{{ $item['url'] }}"
                                       @if ($aktiv) aria-current="page" @endif
                                       class="flex items-center gap-3 rounded-2xl border border-line bg-cream/60 p-2 pr-4 no-underline
                                              aria-[current=page]:border-green">
                                        @include('components.layout.menue-daumen', ['daumen' => $daumen, 'bild' => $item['bild']])
                                        <span class="min-w-0 flex-1">
                                            <span class="block font-medium text-ink">{{ $item['label'] }}</span>
                                            @if ($item['unterzeile'])
                                                <span class="block truncate text-sm text-ink-soft">{{ $item['unterzeile'] }}</span>
                                            @endif
                                        </span>
                                        <x-ui.icon name="arrow-right" :size="18" class="shrink-0 text-green" />
                                    </a>
                                @else
                                    @php
                                        $uebersichtDrin = in_array($item['url'], array_column($item['children'], 'url'), true);
                                        $kacheln = count($item['spalten']) === 1 && ! $item['spalten'][0]['titel'];
                                    @endphp
                                    <details name="mobilmenue" @if ($aktiv) open @endif
                                             class="group/bereich rounded-2xl border border-line bg-cream/60 open:border-green/40 open:bg-cream">
                                        <summary @if ($aktiv) aria-current="true" @endif
                                                 class="flex cursor-pointer list-none items-center gap-3 p-2 pr-4 [&::-webkit-details-marker]:hidden">
                                            @include('components.layout.menue-daumen', ['daumen' => $daumen, 'bild' => $item['bild']])
                                            <span class="min-w-0 flex-1">
                                                <span @class(['block font-medium', 'text-ink' => ! $aktiv, 'text-green' => $aktiv])>{{ $item['label'] }}@if ($aktiv)<span class="sr-only">, {{ __('aktueller Bereich') }}</span>@endif</span>
                                                {{-- Für Vorlesehilfen nicht Teil des Knopfnamens:
                                                     „Wissen, Schaltfläche“ statt des ganzen Satzes. --}}
                                                @if ($item['unterzeile'])
                                                    <span aria-hidden="true" class="block truncate text-sm text-ink-soft">{{ $item['unterzeile'] }}</span>
                                                @endif
                                            </span>
                                            <x-ui.icon name="chevron-down" :size="18"
                                                       class="shrink-0 text-ink-soft transition-transform group-open/bereich:rotate-180" />
                                        </summary>

                                        <div class="px-3 pb-4 pt-1 motion-safe:animate-[menue-ein_.18s_ease-out]">
                                            @unless ($uebersichtDrin)
                                                <a href="{{ $item['url'] }}"
                                                   @if (request()->is(ltrim($item['url'], '/'))) aria-current="page" @endif
                                                   class="mb-3 inline-flex items-center gap-1.5 rounded-full border border-green px-4 py-2 text-sm font-medium text-green no-underline
                                                          hover:bg-green hover:text-on-green focus-visible:bg-green focus-visible:text-on-green">
                                                    {{ __('rahmen.uebersicht') }}: {{ $item['label'] }}
                                                    <x-ui.icon name="arrow-right" :size="16" />
                                                </a>
                                            @endunless

                                            @if ($kacheln)
                                                {{-- Zum Wischen, am Rand angeschnitten, damit man sieht,
                                                     dass es weitergeht. Für Tastatur und Vorlesehilfe ist
                                                     es eine normale Liste von Links. --}}
                                                <ul class="-mx-3 flex snap-x snap-mandatory scroll-px-3 gap-3 overflow-x-auto px-3 pb-2 [scrollbar-width:none]">
                                                    @foreach ($item['spalten'][0]['eintraege'] as $child)
                                                        @php $kk = \App\Support\Titelbilder::klein($child['bild']); @endphp
                                                        <li class="w-36 shrink-0 snap-start sm:w-44">
                                                            <a href="{{ $child['url'] }}"
                                                               @if (request()->is(ltrim($child['url'], '/'))) aria-current="page" @endif
                                                               class="group/kachel block no-underline">
                                                                @if ($kk)
                                                                    <img src="{{ bildadresse($kk) }}" alt="" loading="lazy"
                                                                         class="aspect-[16/10] w-full rounded-xl border border-line object-cover"
                                                                         style="object-position: {{ \App\Support\Titelbilder::fokus($child['bild']) }}">
                                                                @endif
                                                                <span class="mt-2 block text-sm font-medium text-ink group-aria-[current=page]/kachel:text-green">{{ $child['label'] }}</span>
                                                                @if ($child['unterzeile'])
                                                                    <span class="mt-0.5 line-clamp-2 block text-xs text-ink-soft">{{ $child['unterzeile'] }}</span>
                                                                @endif
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                @foreach ($item['spalten'] as $spalte)
                                                    @php $gruppenId = 'mobil-'.($item['schluessel'] ?? $loop->parent->index).'-'.$loop->index; @endphp
                                                    @if ($spalte['titel'])
                                                        <p id="{{ $gruppenId }}" class="mt-3 text-xs font-medium uppercase tracking-[0.12em] text-green first:mt-0">{{ $spalte['titel'] }}</p>
                                                    @endif
                                                    <ul class="mt-1" @if ($spalte['titel']) aria-labelledby="{{ $gruppenId }}" @endif>
                                                        @foreach ($spalte['eintraege'] as $child)
                                                            @php $enkelkinder = array_filter($child['children'] ?? [], fn ($e) => empty($e['nicht_im_menue'])); @endphp
                                                            <li>
                                                                <a href="{{ $child['url'] }}"
                                                                   @if (request()->is(ltrim($child['url'], '/'))) aria-current="page" @endif
                                                                   class="flex items-center justify-between gap-3 border-b border-line/70 py-2.5 text-[0.9375rem] no-underline text-ink
                                                                          aria-[current=page]:font-medium aria-[current=page]:text-green">
                                                                    {{ $child['label'] }}
                                                                    <x-ui.icon name="arrow-right" :size="16" class="shrink-0 text-ink-soft" />
                                                                </a>
                                                                @if ($enkelkinder)
                                                                    <ul class="ml-1 border-l border-line py-1 pl-3">
                                                                        @foreach ($enkelkinder as $enkel)
                                                                            <li>
                                                                                <a href="{{ $enkel['url'] }}"
                                                                                   @if (request()->is(ltrim($enkel['url'], '/'))) aria-current="page" @endif
                                                                                   class="block py-1.5 text-sm no-underline text-ink-soft
                                                                                          aria-[current=page]:font-medium aria-[current=page]:text-green">
                                                                                    {{ $enkel['label'] }}
                                                                                </a>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endforeach
                                            @endif
                                        </div>
                                    </details>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    {{-- Der Notausgang steht dauerhaft im Kopf und in der unteren
                         Leiste — hier im Menü wäre er ein dritter, versteckter Ort.
                         Die Sprachwahl bleibt: In der Kopfzeile ist sie erst ab
                         „sm“ sichtbar, unterhalb also nur hier. --}}
                    <div class="mx-auto mt-4 max-w-2xl border-t border-line pt-3 sm:hidden">
                        <x-layout.sprachumschalter :fassungen="$fassungen ?? []" variant="menue" />
                    </div>
                </nav>
            </details>
        </div>
    </div>
</header>
