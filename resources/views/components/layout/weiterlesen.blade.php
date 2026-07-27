@props([
    'seiten' => [],
    'bereich' => null,
])

@if (count($seiten) > 0)
    {{--
        Weiterführung am Seitenende.

        Ohne sie endet jede Unterseite in einer Sackgasse: Wer bei „Satzung"
        gelandet ist, müsste zurück ins Menü, um „Mitgliedschaft" zu finden.
        Die Einträge stammen aus der Navigation — nichts davon ist zusätzlich
        gepflegter Inhalt.
    --}}
    <aside class="border-t border-line bg-card px-4 py-8 lg:px-10 lg:py-10"
           aria-labelledby="weiterlesen-titel">
        <div class="mx-auto max-w-6xl">
            <h2 id="weiterlesen-titel" class="mb-5 font-display text-xl font-medium text-ink">
                {{ $bereich
                    ? __('rahmen.weiterlesen.mehr_zu', ['bereich' => $bereich])
                    : __('rahmen.weiterlesen.auch_interessant') }}
            </h2>

            <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($seiten as $seite)
                    <li class="flex">
                        <a href="{{ $seite['url'] }}"
                           class="group flex flex-1 items-center gap-3 rounded-card border border-line
                                  bg-cream px-4 py-3.5 no-underline hover:border-green">
                            <span class="flex-1 text-[0.9375rem] text-ink group-hover:underline">
                                {{ $seite['label'] }}
                            </span>
                            <span class="shrink-0 text-ink-soft transition-transform group-hover:translate-x-0.5"
                                  aria-hidden="true">→</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </aside>
@endif
