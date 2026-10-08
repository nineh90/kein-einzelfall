@props([
    'titel' => 'Du brauchst jetzt Hilfe?',
    'kompakt' => false,   // true = Kurzfassung der Startseite, zwei Spalten
])

@php
    $alle = config('hilfe.nummern');

    // Kurzfassung: zwei Spalten, wie vom Verein gewünscht (KEV-46). Die
    // ausführliche Fassung bleibt eine Liste, sie steht auch in schmalen
    // Spalten (Fehlerseite).
    $spalten = $kompakt
        ? array_map(
            // In der Reihenfolge der Aufteilung, nicht der Gesamtliste.
            fn (array $schluessel) => array_values(array_filter(array_map(fn ($s) => $alle[$s] ?? null, $schluessel))),
            array_values(config('hilfe.kompakt')),
        )
        : [array_values($alle)];

    $notrufe = config('hilfe.notruf');
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
        Diese Stellen sind unabhängig von uns erreichbar — kostenfrei und auf Wunsch anonym.
    </p>

    <div @class(['grid gap-3', 'md:grid-cols-2 md:gap-x-10' => count($spalten) > 1])>
        @foreach ($spalten as $nummern)
            {{-- Auf dem Handy laufen beide Spalten als eine Liste untereinander.
                 Die Trennlinie zwischen ihnen hält die letzte Nummer der ersten
                 Spalte, sonst fehlte sie genau dort. --}}
            <ul @class([
                'flex flex-col gap-3',
                'max-md:border-b max-md:border-green/20 max-md:pb-3' => ! $loop->last,
            ])>
                @foreach ($nummern as $n)
                    <li class="flex flex-col gap-0.5 border-b border-green/20 pb-3 last:border-0 last:pb-0">
                        <a href="tel:{{ $n['tel'] }}"
                           class="font-display text-lg font-medium text-green-deep no-underline hover:underline">
                            {{ $n['nummer'] }}
                            <span class="sr-only">– {{ $n['name'] }} anrufen</span>
                        </a>
                        <span class="text-sm text-ink">{{ $n['name'] }}</span>
                        <span class="text-xs text-ink-soft">{{ implode(' · ', $n['angaben']) }}</span>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </div>

    <p class="mt-5 flex flex-wrap items-baseline gap-x-5 gap-y-1 border-t border-green/30 pt-4 text-sm text-ink">
        <span>Bei unmittelbarer Gefahr:</span>
        @foreach ($notrufe as $notruf)
            <span>
                <a href="tel:{{ $notruf['tel'] }}"
                   class="font-display text-lg font-medium text-alert no-underline hover:underline">
                    {{ $notruf['nummer'] }}
                </a>
                <span class="text-ink-soft [overflow-wrap:anywhere]">{{ $notruf['name'] }}</span>
            </span>
        @endforeach
    </p>
</section>
