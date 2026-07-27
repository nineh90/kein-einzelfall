@extends('layouts.app')

@section('title', $termin->titel)
@section('description', $termin->teaser ?: 'Termin des KE!N EINZELFALL e.V.')

@php
    // Event-Auszeichnung: fehlt auf der Altseite komplett. Damit können
    // Suchmaschinen Termine direkt im Ergebnis anzeigen.
    $strukturierteDaten = json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $termin->titel,
        'startDate' => $termin->zeitMaschinenlesbar(),
        'endDate' => $termin->endet_am?->toIso8601String(),
        'eventAttendanceMode' => $termin->online
            ? 'https://schema.org/OnlineEventAttendanceMode'
            : 'https://schema.org/OfflineEventAttendanceMode',
        'eventStatus' => 'https://schema.org/EventScheduled',
        'description' => $termin->teaser,
        'location' => $termin->online
            ? ['@type' => 'VirtualLocation', 'url' => route('events.show', $termin->slug)]
            : array_filter([
                '@type' => 'Place',
                'name' => $termin->ort,
                'address' => $termin->adresse,
            ]),
        'organizer' => [
            '@type' => 'Organization',
            'name' => 'KE!N EINZELFALL e.V.',
            'url' => url('/'),
        ],
    ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
@endphp

@push('head')
    <script type="application/ld+json" @isset($cspNonce) nonce="{{ $cspNonce }}" @endisset>
        {!! $strukturierteDaten !!}
    </script>
@endpush

@section('content')
<article class="px-4 py-8 lg:px-10 lg:py-12">
    <div class="mx-auto max-w-3xl">

        <nav aria-label="Brotkrumen" class="mb-6 text-sm">
            <a href="{{ route('events.index') }}" class="text-green-deep no-underline hover:underline">
                Veranstaltungen
            </a>
        </nav>

        <div class="flex flex-wrap items-center gap-2">
            @if ($termin->art)
                <span class="rounded-full bg-green-mist px-3 py-1 text-xs text-green-deep">
                    {{ $termin->art }}
                </span>
            @endif
            @if ($termin->online)
                <span class="rounded-full border border-line px-3 py-1 text-xs text-ink-soft">Online</span>
            @endif
        </div>

        <h1 class="mt-2 font-display text-[1.75rem] font-medium leading-tight text-ink lg:text-4xl">
            {{ $termin->titel }}
        </h1>

        <dl class="mt-6 flex flex-col gap-3 rounded-card border border-line bg-card px-5 py-4">
            <div class="flex flex-wrap gap-x-3">
                <dt class="w-24 shrink-0 text-sm text-ink-soft">Wann</dt>
                <dd class="text-ink">
                    <time datetime="{{ $termin->zeitMaschinenlesbar() }}">{{ $termin->zeitraum() }}</time>
                </dd>
            </div>

            @if ($termin->ort || $termin->online)
                <div class="flex flex-wrap gap-x-3">
                    <dt class="w-24 shrink-0 text-sm text-ink-soft">Wo</dt>
                    <dd class="text-ink">
                        {{ $termin->online ? 'Online' : $termin->ort }}
                        @if ($termin->adresse && ! $termin->online)
                            <span class="block text-sm text-ink-soft">{{ $termin->adresse }}</span>
                        @endif
                    </dd>
                </div>
            @endif
        </dl>

        <div class="mt-4 flex flex-wrap gap-3">
            <x-ui.button :href="route('events.ical.einzeln', $termin->slug)" variant="ghost" size="sm">
                In meinen Kalender eintragen
            </x-ui.button>

            @if ($termin->anmeldung_url)
                <x-ui.button :href="$termin->anmeldung_url" variant="primary" size="sm">
                    Zur Anmeldung
                </x-ui.button>
            @endif
        </div>

        @if ($termin->anmeldung_hinweis)
            <p class="mt-3 text-sm text-ink-soft">{{ $termin->anmeldung_hinweis }}</p>
        @endif

        @if ($termin->beschreibung)
            <div class="mt-8 max-w-prose leading-relaxed text-ink
                        [&_a]:text-green-deep [&_a]:underline
                        [&_h2]:mt-6 [&_h2]:font-display [&_h2]:text-xl
                        [&_li]:mb-1 [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-5">
                {!! $termin->beschreibung !!}
            </div>
        @endif
    </div>
</article>
@endsection
