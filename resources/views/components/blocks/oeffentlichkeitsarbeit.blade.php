@props([
    'titel' => null,
    'einleitung' => null,
    'art' => 'messe',  // Schlüssel aus App\Models\OutreachItem::ARTEN
    'auf' => 'cream',  // cream | card
])

@php
    $eintraege = \App\Models\OutreachItem::veroeffentlicht()->vonArt($art)->neuesteZuerst()->get();

    // Kästen stehen auf der jeweils anderen Fläche, sonst verschwämmen sie
    // auf der Karte mit dem Hintergrund.
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';

    // Ohne Überschrift des Abschnitts hängen die Karten direkt unter dem
    // Seitentitel und sind selbst Abschnitte.
    $ebene = $titel ? 'h3' : 'h2';
    $anker = $titel ? 'oa-'.Str::slug($titel) : null;
@endphp

@if ($eintraege->isNotEmpty())
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
--}}
<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])
         @if ($anker) aria-labelledby="{{ $anker }}" @endif>
    <div class="mx-auto max-w-6xl">

        @if ($titel || $einleitung)
            <div class="max-w-prose">
                @if ($titel)
                    <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
                    <h2 id="{{ $anker }}" class="mb-4 scroll-mt-24 font-display text-2xl font-medium text-green lg:text-3xl">
                        {{ $titel }}
                    </h2>
                @endif

                @if ($einleitung)
                    <p class="mb-8 leading-relaxed text-ink-soft">{{ $einleitung }}</p>
                @endif
            </div>
        @endif

        <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($eintraege as $eintrag)
                @php($foto = $eintrag->titelfoto())
                <li>
                    <article class="group relative flex h-full flex-col overflow-hidden rounded-card border border-line {{ $innen }}
                                    focus-within:ring-2 focus-within:ring-green hover:border-green-brand">
                        @if ($foto)
                            <img src="{{ bildadresse($foto['bild']) }}" alt="" loading="lazy"
                                 class="aspect-[4/3] w-full object-cover">
                        @endif

                        <div class="flex flex-1 flex-col p-5">
                            <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-ink-soft">
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
    </div>
</section>
@endif
