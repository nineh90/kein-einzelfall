@props([
    'titel' => null,
    'einleitung' => null,
    'bereich' => null,      // nur einen Bereich zeigen (z.B. "Vorstand")
    'auf' => 'cream',
])

@php
    $personen = \App\Models\TeamMember::veroeffentlicht()
        ->when($bereich, fn ($q) => $q->where('bereich', $bereich))
        ->orderBy('position')
        ->orderBy('name')
        ->get();
@endphp

@if ($personen->isNotEmpty())
    {{--
        Vorstand und Team.

        Die Selbstvorstellungen umfassen 18 bis 19 Absätze pro Person — als
        durchlaufender Text erschlagen sie die Seite. Deshalb: Kurzangaben
        sichtbar, der ausführliche Text aufklappbar.

        Natives <details> statt Alpine: ohne JavaScript bedienbar, und der
        Volltext bleibt im Dokument und damit für Suchmaschinen sichtbar.
    --}}
    <section @class([
        'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
        'bg-card border-y border-line' => $auf === 'card',
    ])
    {{-- Mehrere Raster auf einer Seite (Vorstand, Team, …) brauchen
         verschiedene Kennungen; ohne Überschrift benennt der Bereich den
         Abschnitt. --}}
    @php $kennung = 'team-'.\Illuminate\Support\Str::slug($bereich ?: 'alle').'-titel'; @endphp
    @if ($titel) aria-labelledby="{{ $kennung }}" @else aria-label="{{ $bereich ?: 'Vorstand und Team' }}" @endif>
        <div class="mx-auto max-w-6xl">
            @if ($titel)
                <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
                <h2 id="{{ $kennung }}" class="mb-4 font-display text-2xl font-medium text-ink lg:text-3xl">
                    {{ $titel }}
                </h2>
            @endif

            @if ($einleitung)
                <p class="mb-8 max-w-prose leading-relaxed text-ink-soft">{{ $einleitung }}</p>
            @endif

            {{-- team-raster: Öffnet sich eine Karte, streckt sich die Nachbarin
                 in derselben Zeile nicht mit (KEV-21, Regel in app.css). --}}
            <ul class="team-raster grid gap-5 md:grid-cols-2">
                @foreach ($personen as $person)
                    <li class="flex">
                        <article id="{{ $person->anker() }}"
                                 class="flex flex-1 scroll-mt-24 flex-col overflow-hidden rounded-card
                                        border border-line bg-cream">

                            {{-- Auf dem Handy steht das Kurzprofil unter Foto und Name
                                 über die volle Kartenbreite (KEV-26). Neben dem Foto
                                 blieben dort rund 190 px, und ein Absatz lief über
                                 zwanzig Zeilen. Ab „sm“ wieder rechts neben dem Foto. --}}
                            <div class="grid grid-cols-[auto_minmax(0,1fr)] items-center gap-x-4 gap-y-3 p-5
                                        sm:items-start sm:gap-y-2">
                                <div class="shrink-0 sm:row-span-2">
                                    @if ($person->foto_pfad)
                                        <img src="{{ $person->foto_pfad }}" alt="{{ $person->foto_alt }}"
                                             loading="lazy"
                                             class="h-20 w-20 rounded-full border border-line object-cover">
                                    @else
                                        {{-- Kein Platzhalter-Gesicht: Initialen sind
                                             ehrlicher als ein Symbol, das eine Person
                                             darstellen soll, die es so nicht gibt. --}}
                                        <span aria-hidden="true"
                                              class="flex h-20 w-20 items-center justify-center rounded-full
                                                     border border-line bg-green-mist font-display text-xl
                                                     text-green-deep">
                                            {{ collect(explode(' ', $person->name))
                                                ->filter()->take(2)
                                                ->map(fn ($t) => mb_substr($t, 0, 1))->implode('') }}
                                        </span>
                                    @endif
                                </div>

                                <div>
                                    @if ($person->rolle)
                                        {{-- Auf dem Handy etwas kleiner und enger gesperrt:
                                             „DATENSCHUTZBEAUFTRAGTE“ passte sonst nicht neben
                                             das Foto und brach vor dem letzten Buchstaben um. --}}
                                        <p class="hyphens-auto text-[0.6875rem] uppercase tracking-[0.06em] text-green sm:text-xs sm:tracking-[0.1em]">
                                            {{ $person->rolle }}
                                        </p>
                                    @endif

                                    <h3 class="mt-0.5 font-display text-lg font-semibold text-ink">
                                        {{ $person->name }}
                                    </h3>

                                    @if ($person->untertitel)
                                        <p class="mt-0.5 text-sm text-ink-soft">{{ $person->untertitel }}</p>
                                    @endif
                                </div>

                                @if ($person->kurzprofil)
                                    <p class="col-span-2 text-sm leading-relaxed text-ink-soft sm:col-span-1 sm:col-start-2">
                                        {{ $person->kurzprofil }}
                                    </p>
                                @endif
                            </div>

                            @if ($person->hatProfil())
                                {{-- mt-auto: In einer gleich hohen Zeile stehen die
                                     „Mehr über …“-Links beider Karten auf einer Linie. --}}
                                <details class="group mt-auto border-t border-line">
                                    <summary class="flex cursor-pointer items-center gap-2 px-5 py-3
                                                    text-sm text-green-deep marker:content-none
                                                    [&::-webkit-details-marker]:hidden">
                                        <span class="flex-1">
                                            <span class="group-open:hidden">Mehr über {{ $person->rufname() }} lesen</span>
                                            <span class="hidden group-open:inline">Weniger anzeigen</span>
                                        </span>
                                        <span class="transition-transform group-open:rotate-180">
                                            <x-ui.icon name="chevron-down" :size="18" />
                                        </span>
                                    </summary>

                                    <div class="border-t border-line px-5 py-4 text-sm leading-relaxed text-ink-soft
                                                [&_a]:text-green-deep [&_a]:underline
                                                [&_p]:mb-3 [&_p:last-child]:mb-0">
                                        {!! $person->profil !!}
                                    </div>
                                </details>
                            @endif
                        </article>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
