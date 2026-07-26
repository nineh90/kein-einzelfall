@props([
    'titel' => 'Du brauchst jetzt Hilfe?',
    'kompakt' => false,   // true = nur die zwei wichtigsten Nummern
])

@php
    $nummern = config('hilfe.nummern');
    if ($kompakt) {
        $nummern = array_slice($nummern, 0, 2);
    }
    $notruf = config('hilfe.notruf');
@endphp

{{--
    Hilfe-Box.

    Ruhig gestaltet, nicht alarmierend — die Zielgruppe ist ohnehin belastet.
    Deshalb Grün statt Rot und sachliche Sprache.

    Die Nummern sind tel:-Links: mobil ein Tipp zum Anrufen, und das ist der Fall,
    für den diese Box existiert. Als <section> mit Überschrift ausgezeichnet, damit
    Screenreader-Nutzer sie über die Landmarken-Navigation direkt anspringen können.
--}}
<section aria-labelledby="hilfe-titel"
         class="rounded-card border-2 border-green bg-green-mist px-5 py-6">

    <h2 id="hilfe-titel" class="mb-1 font-display text-xl font-medium text-green-deep">
        {{ $titel }}
    </h2>
    <p class="mb-5 text-sm text-ink-soft">
        Diese Stellen sind unabhängig von uns erreichbar — kostenlos und auf Wunsch anonym.
    </p>

    <ul class="flex flex-col gap-3">
        @foreach ($nummern as $n)
            <li class="flex flex-col gap-0.5 border-b border-green/20 pb-3 last:border-0 last:pb-0">
                <a href="tel:{{ $n['tel'] }}"
                   class="font-display text-lg font-medium text-green-deep no-underline hover:underline">
                    {{ $n['nummer'] }}
                    <span class="sr-only">– {{ $n['name'] }} anrufen</span>
                </a>
                <span class="text-sm text-ink">{{ $n['name'] }}</span>
                <span class="text-xs text-ink-soft">
                    {{ $n['zeiten'] }} · {{ $n['hinweis'] }}
                </span>
            </li>
        @endforeach
    </ul>

    <p class="mt-5 border-t border-green/30 pt-4 text-sm text-ink">
        Bei unmittelbarer Gefahr:
        <a href="tel:{{ $notruf['tel'] }}"
           class="font-display text-lg font-medium text-alert no-underline hover:underline">
            {{ $notruf['nummer'] }}
        </a>
        <span class="text-ink-soft">({{ $notruf['name'] }})</span>
    </p>
</section>
