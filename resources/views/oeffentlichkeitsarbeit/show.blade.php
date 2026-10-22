@extends('layouts.app')

@section('title', $eintrag->titel)
@section('description', \Illuminate\Support\Str::limit($eintrag->anriss(), 155))

@php
    use App\Support\Medien;

    $uebersicht = sprachlink('page', ['slug' => 'oeffentlichkeitsarbeit']);

    $krumen = [
        ['label' => __('rahmen.start'), 'url' => \App\Models\Language::aktuell()->pfad('/')],
        ['label' => __('Öffentlichkeitsarbeit'), 'url' => $uebersicht],
        ['label' => $eintrag->titel, 'url' => null],
    ];

    $fotos = $eintrag->fotoListe();
    $erstes = array_shift($fotos);

    $extern = (bool) preg_match('#^https?://#i', (string) $eintrag->link_url);

    // Die anderen Einträge und die Übersicht als Weiterführung am Seitenende
    $andere = $weitere->map(fn ($e) => ['label' => $e->titel, 'url' => $e->url()])
        ->push(['label' => __('Alle Einträge der Öffentlichkeitsarbeit'), 'url' => $uebersicht])
        ->all();
@endphp

@section('content')
{{--
    Ein Eintrag der Öffentlichkeitsarbeit (KEV-134), etwa eine Messe. Aufbau
    wie bei einer Gruppe ohne Titelbild: Art, Titel, wann und wo, dann das
    erste Foto groß, der Text und die übrigen Fotos.

    Kein Seitenkopf mit Bild: Der ist für die ruhigen Stimmungsbilder gebaut,
    Titel und Text liegen darauf. Auf einem echten Foto vom Messestand wäre
    der Text kaum zu lesen, und die Menschen darauf verschwänden unter dem
    Schleier.
--}}
<article class="px-4 md:px-8 pb-10 pt-8 lg:px-10 lg:pb-14 lg:pt-12">
    {{-- Galerie (KEV-132): Jedes Foto ist ein Link auf die große Fassung.
         galerie.js öffnet sie in einem Dialog mit Vor und Zurück; ohne
         JavaScript öffnet der Link einfach das Bild. Die Beschriftungen kommen
         von hier, damit sie übersetzt sind. --}}
    <div class="mx-auto max-w-3xl"
         data-galerie
         data-titel="{{ __('Fotos') }}"
         data-schliessen="{{ __('Schließen') }}"
         data-zurueck="{{ __('Vorheriges Foto') }}"
         data-weiter="{{ __('Nächstes Foto') }}"
         data-zaehler="{{ __(':nr von :gesamt') }}">

        <x-ui.brotkrumen :krumen="$krumen" />

        @if ($eintrag->artName())
            <span class="rounded-full bg-green-mist px-3 py-1 text-xs text-green-deep">{{ $eintrag->artName() }}</span>
        @endif

        <h1 class="mt-3 font-display text-[1.75rem] font-medium leading-tight text-green lg:text-4xl">
            {{ $eintrag->titel }}
        </h1>

        @if ($eintrag->datum || $eintrag->ort)
            <p class="mt-2 flex flex-wrap items-center gap-x-2 text-ink-soft">
                @if ($eintrag->datum)
                    <time datetime="{{ $eintrag->datum->toDateString() }}">{{ $eintrag->datumLang() }}</time>
                @endif
                @if ($eintrag->datum && $eintrag->ort)
                    <span aria-hidden="true">·</span>
                @endif
                @if ($eintrag->ort)
                    <span>{{ $eintrag->ort }}</span>
                @endif
            </p>
        @endif

        @if ($erstes)
            @php($masse = Medien::masse($erstes['bild']))
            <a href="{{ bildadresse($erstes['bild']) }}" data-galerie-bild
               class="mt-6 block overflow-hidden rounded-card border border-line focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green">
                <img src="{{ bildadresse($erstes['bild']) }}" alt="{{ $erstes['alt'] }}"
                     @if ($masse) width="{{ $masse[0] }}" height="{{ $masse[1] }}" @endif
                     fetchpriority="high"
                     class="h-auto w-full">
                <span class="sr-only">{{ __('Foto vergrößern') }}</span>
            </a>
        @endif

        @if (filled(strip_tags((string) $eintrag->beschreibung)))
            <div class="mt-8 max-w-prose text-[1.0625rem] leading-relaxed text-ink
                        [&_a]:text-green-deep [&_a]:underline [&_p]:mb-4 [&_strong]:font-semibold
                        [&_ul]:mb-4 [&_ul]:list-disc [&_ul]:pl-5">
                {!! $eintrag->beschreibung !!}
            </div>
        @endif

        @if ($eintrag->link_url)
            <div class="mt-4">
                <x-ui.button :href="$eintrag->link_url" variant="primary" size="sm"
                             :rel="$extern ? 'noreferrer noopener' : null">
                    {{ $eintrag->link_text ?: __('Mehr erfahren') }}
                    @if ($extern)
                        <span class="sr-only">{{ __('rahmen.partner.fremde_seite') }}</span>
                    @endif
                </x-ui.button>
            </div>
        @endif

        @if ($fotos)
            {{-- Gleichmäßiges Raster aus Vorschaubildern (KEV-132): Mit sechs
                 Hochformaten untereinander wurde die Seite sehr lang. Die
                 Vorschau schneidet zu (3:4), groß steht jedes Foto ganz da. --}}
            <section class="mt-10" aria-labelledby="weitere-fotos">
                <h2 id="weitere-fotos" class="mb-4 font-display text-xl font-medium text-green">{{ __('Weitere Fotos') }}</h2>
                <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($fotos as $foto)
                        <li>
                            <a href="{{ bildadresse($foto['bild']) }}" data-galerie-bild
                               class="group block overflow-hidden rounded-card border border-line focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green">
                                <img src="{{ bildadresse($foto['bild']) }}" alt="{{ $foto['alt'] }}" loading="lazy"
                                     class="aspect-[3/4] w-full object-cover transition-opacity group-hover:opacity-90">
                                <span class="sr-only">{{ __('Foto vergrößern') }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</article>

<x-layout.weiterlesen :seiten="$andere" :titel="__('Mehr Öffentlichkeitsarbeit')" auf="card" />
@endsection
