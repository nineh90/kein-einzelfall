@props([
    'titel' => null,
    'einleitung' => null,
    'eintraege' => [],    // [['frage'=>, 'antwort'=>], ...]
    'auf' => 'cream',
])

@php
    // FAQ-Auszeichnung für Suchmaschinen. Fragen und Antworten können damit
    // direkt im Suchergebnis erscheinen — gerade bei Behördenthemen erreicht
    // man so Menschen, die gar nicht nach dem Verein gesucht haben.
    $faqDaten = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($eintraege)->map(fn ($e) => [
            '@type' => 'Question',
            'name' => $e['frage'] ?? '',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($e['antwort'] ?? '')],
        ])->all(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
@endphp

@if (count($eintraege) > 0)
    @push('head')
        <script type="application/ld+json" @isset($cspNonce) nonce="{{ $cspNonce }}" @endisset>
            {!! $faqDaten !!}
        </script>
    @endpush
@endif

{{--
    Aufklappbare Fragen und Antworten.

    Natives <details>: ohne JavaScript bedienbar, Screenreader kennen das
    Muster und melden auf/zu von selbst, Tastaturbedienung kommt gratis.
    Der Inhalt bleibt im Dokument und damit auch für Suchmaschinen sichtbar —
    zugeklappt heisst hier nur „nicht angezeigt", nicht „nicht vorhanden".
--}}
<section @class([
    'px-4 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div class="mx-auto max-w-6xl">
        <div class="max-w-prose">
            @if ($titel)
                <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
                <h2 class="mb-4 font-display text-2xl font-medium text-ink lg:text-3xl">{{ $titel }}</h2>
            @endif

            @if ($einleitung)
                <p class="mb-6 leading-relaxed text-ink-soft">{{ $einleitung }}</p>
            @endif

            <div class="flex flex-col gap-2">
                @foreach ($eintraege as $eintrag)
                    <details class="group rounded-card border border-line bg-cream
                                    open:border-green open:bg-card">
                        <summary class="flex cursor-pointer items-start gap-3 px-5 py-4
                                        marker:content-none [&::-webkit-details-marker]:hidden">
                            <span class="flex-1 font-medium text-ink">{{ $eintrag['frage'] ?? '' }}</span>
                            <span class="mt-0.5 shrink-0 text-ink-soft transition-transform group-open:rotate-180">
                                <x-ui.icon name="chevron-down" :size="20" />
                            </span>
                        </summary>

                        <div class="border-t border-line px-5 py-4 leading-relaxed text-ink-soft
                                    [&_a]:text-green-deep [&_a]:underline
                                    [&_li]:mb-1 [&_p]:mb-3 [&_p:last-child]:mb-0 [&_ul]:list-disc [&_ul]:pl-5">
                            {!! $eintrag['antwort'] ?? '' !!}
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </div>
</section>
