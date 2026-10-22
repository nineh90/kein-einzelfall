@props([
    'titel' => null,
    'einleitung' => null,
    'arten' => null,   // Schlüssel aus App\Models\OutreachItem::ARTEN (KEV-136)
    'art' => null,     // bis KEV-136: ein einzelner Schlüssel
    // KEV-135: Untergruppen mit eigener Überschrift, Einleitung und Arten
    // (Petitionen: „Initiierte“ und „Unterstützte“). Leer = eine Gruppe aus `arten`.
    'gruppen' => [],
    'bild' => null,    // KEV-135: Bild neben der Einleitung, Schmuck
    'bild_alt' => null,
    'auf' => 'cream',  // cream | card
])

@php
    use App\Models\OutreachItem;
    use App\Models\PageBlock;

    // Je Gruppe ihre Einträge, neueste zuerst. Gruppen ohne Eintrag fallen weg.
    $gruppen = collect(PageBlock::oeffentlichkeitsarbeitGruppen(compact('arten', 'art', 'gruppen')))
        ->map(fn ($g) => $g + [
            'eintraege' => OutreachItem::veroeffentlicht()->vonArt($g['arten'])->neuesteZuerst()->get(),
        ])
        ->filter(fn ($g) => $g['eintraege']->isNotEmpty())
        ->values();

    // Jede Zeile einer Einleitung ein Absatz; *fett* und E-Mail-Adressen als
    // Link wie im Textbaustein (Presse: „Presseanfragen bitte an: presse@…“).
    $absaetze = fn (?string $text) => preg_split('/\R+/u', trim((string) $text), flags: PREG_SPLIT_NO_EMPTY);
    $einleitung = $absaetze($einleitung);

    // Kästen stehen auf der jeweils anderen Fläche, sonst verschwämmen sie
    // auf der Karte mit dem Hintergrund.
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';

    $anker = $titel ? 'oa-'.Str::slug($titel) : null;
@endphp

@if ($gruppen->isNotEmpty())
{{--
    Messen, Petitionen, Presse … als kleine Karten (KEV-134), je Art ein
    Abschnitt. Die Übersicht soll sich überfliegen lassen; der ganze Text und
    alle Fotos stehen auf der Seite des Eintrags.

    - Die ganze Karte ist anklickbar, der Link sitzt aber auf dem Titel: Eine
      Vorlesehilfe liest so „Ehrenamtsmesse Bayreuth, Link“ statt dreimal
      „Mehr erfahren“. „Mehr erfahren“ ist nur sichtbar, nicht vorgelesen.
    - Das Foto ist auf der Karte Schmuck (alt=""), beschrieben wird es auf
      der Seite des Eintrags. Hier stünde die Beschreibung vor dem Titel.
    - Ohne Foto bleibt eine Textkarte.
    - Untergruppen (KEV-135) sind h3 unter der Überschrift des Abschnitts,
      ihre Karten dann h4. So bleibt die Gliederung für Vorlesehilfen stimmig.
--}}
<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])
         @if ($anker) aria-labelledby="{{ $anker }}" @endif>
    <div class="mx-auto max-w-6xl">

        @if ($titel || $einleitung || $bild)
            {{-- Mit Bild zweispaltig ab „lg“, Text links, Bild rechts; darunter
                 steht das Bild unter dem Text. Ohne Bild wie bisher. --}}
            <div @class([
                'mb-8',
                'grid items-center gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.9fr)] lg:gap-12' => $bild,
            ])>
                <div class="max-w-prose">
                    @if ($titel)
                        <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
                        <h2 id="{{ $anker }}" class="mb-4 scroll-mt-24 font-display text-2xl font-medium text-green lg:text-3xl">
                            {{ $titel }}
                        </h2>
                    @endif

                    @if ($einleitung)
                        <div class="flex flex-col gap-3 leading-relaxed text-ink-soft [&_a]:text-green-deep [&_a]:underline">
                            @foreach ($einleitung as $absatz)
                                <p>{{ hervorheben($absatz) }}</p>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($bild)
                    @php $masse = \App\Support\Medien::masse($bild); @endphp
                    <img src="{{ bildadresse($bild) }}" alt="{{ $bild_alt ?? '' }}" loading="lazy"
                         @if ($masse) width="{{ $masse[0] }}" height="{{ $masse[1] }}" @endif
                         class="h-auto w-full rounded-card border border-line">
                @endif
            </div>
        @endif

        @foreach ($gruppen as $gruppe)
            @php
                // Ohne Überschrift des Abschnitts hängen die Karten direkt unter
                // dem Seitentitel und sind selbst Abschnitte.
                $gruppenEbene = $titel ? 'h3' : 'h2';
                $ebene = match (true) {
                    filled($gruppe['titel']) => $titel ? 'h4' : 'h3',
                    default => $titel ? 'h3' : 'h2',
                };

                // Zeigt eine Gruppe mehrere Arten (Presse: Artikel und
                // Interviews), trägt jede Karte ein Schild. Bei nur einer sagt
                // es die Überschrift.
                $mitSchild = count($gruppe['arten']) > 1;
            @endphp

            @if (filled($gruppe['titel']))
                <{{ $gruppenEbene }} id="oa-{{ Str::slug($gruppe['titel']) }}" @class([
                    'mb-3 scroll-mt-24 font-display text-xl font-medium text-ink lg:text-2xl',
                    'mt-12' => ! $loop->first,
                ])>{{ $gruppe['titel'] }}</{{ $gruppenEbene }}>

                @if ($gruppeEinleitung = $absaetze($gruppe['einleitung']))
                    <div class="mb-6 flex max-w-prose flex-col gap-3 leading-relaxed text-ink-soft [&_a]:text-green-deep [&_a]:underline">
                        @foreach ($gruppeEinleitung as $absatz)
                            <p>{{ hervorheben($absatz) }}</p>
                        @endforeach
                    </div>
                @endif
            @endif

            <ul @class(['grid gap-5 sm:grid-cols-2 lg:grid-cols-3', 'mt-10' => ! $loop->first && blank($gruppe['titel'])])>
                @foreach ($gruppe['eintraege'] as $eintrag)
                    @php $foto = $eintrag->titelfoto(); @endphp
                    <li>
                        <article class="group relative flex h-full flex-col overflow-hidden rounded-card border border-line {{ $innen }}
                                        focus-within:ring-2 focus-within:ring-green hover:border-green-brand">
                            @if ($foto)
                                <img src="{{ bildadresse($foto['bild']) }}" alt="" loading="lazy"
                                     class="aspect-[4/3] w-full object-cover">
                            @endif

                            <div class="flex flex-1 flex-col p-5">
                                <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-ink-soft">
                                    @if ($mitSchild && $eintrag->artName())
                                        <span class="rounded-full bg-green-mist px-2.5 py-0.5 text-[0.6875rem] text-green-deep">{{ $eintrag->artName() }}</span>
                                    @endif
                                    @if ($eintrag->datum)
                                        <time datetime="{{ $eintrag->datum->toDateString() }}">{{ $eintrag->datumKurz() }}</time>
                                    @endif
                                    @if ($eintrag->datum && $eintrag->ort)
                                        <span aria-hidden="true">·</span>
                                    @endif
                                    @if ($eintrag->ort)
                                        <span>{{ $eintrag->ort }}</span>
                                    @endif
                                </p>

                                <{{ $ebene }} class="mt-1 font-display text-lg font-semibold leading-snug text-ink">
                                    {{-- after:absolute inset-0: der Link deckt die ganze Karte ab --}}
                                    <a href="{{ $eintrag->url() }}"
                                       class="no-underline after:absolute after:inset-0 after:content-[''] focus-visible:outline-none group-hover:underline">
                                        {{ $eintrag->titel }}
                                    </a>
                                </{{ $ebene }}>

                                @if ($anriss = $eintrag->anriss())
                                    <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-ink-soft">{{ $anriss }}</p>
                                @endif

                                <p aria-hidden="true" class="mt-auto pt-4 text-sm font-medium text-green-deep">
                                    {{ __('Mehr erfahren') }} <span class="inline-block transition-transform group-hover:translate-x-0.5">→</span>
                                </p>
                            </div>
                        </article>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </div>
</section>
@endif
