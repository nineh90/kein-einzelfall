@props([
    'titel' => null,
    'dokumente' => [],   // [['titel' =>, 'url' =>, 'bytes' =>, 'typ' =>], ...]
])

@php
    $groesse = function (?int $b): string {
        if (! $b) return '';
        return $b >= 1048576
            ? number_format($b / 1048576, 1, ',', '.').' MB'
            : number_format($b / 1024, 0, ',', '.').' KB';
    };
@endphp

{{--
    Download-Liste.

    Barrierefreiheits-Punkte, die hier zählen:
    - Der Linktext ist der Dokumenttitel, nie der Dateiname. "6.5.3.1.-Info-
      Erwerbsminderung-Deine-Zweigstelle.pdf" vorgelesen zu bekommen ist zumutbar
      für niemanden.
    - Dateityp und Größe stehen im Linktext (WCAG 3.2.5): wer über Mobilfunk liest,
      soll vor dem Tippen wissen, was auf ihn zukommt.
    - Kein target="_blank": ungefragte neue Tabs sind desorientierend. Wer will,
      öffnet selbst in einem neuen Tab.
--}}
<section @if ($titel) aria-labelledby="dl-{{ Str::slug($titel) }}" @else aria-label="Dokumente zum Herunterladen" @endif>
    @if ($titel)
        <h2 id="dl-{{ Str::slug($titel) }}" class="mb-4 font-display text-2xl font-medium text-ink">
            {{ $titel }}
        </h2>
    @endif

    <ul class="flex flex-col divide-y divide-line border-y border-line">
        @foreach ($dokumente as $dok)
            @php
                $typ = strtoupper($dok['typ'] ?? pathinfo($dok['url'], PATHINFO_EXTENSION) ?: 'PDF');
                $gr = $groesse($dok['bytes'] ?? null);
                // Zusammengesetzt in PHP: eine @if-Direktive direkt an Text geklebt
                // ("...Datei@if") erkennt Blade nicht als Direktive.
                $meta = $gr ? "{$typ}-Datei, {$gr}" : "{$typ}-Datei";
            @endphp
            <li>
                <a href="{{ $dok['url'] }}"
                   download
                   class="group flex items-center gap-4 py-4 no-underline hover:bg-card">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg
                                 bg-green-mist font-display text-[0.6875rem] font-semibold text-green-deep">
                        {{ $typ }}
                    </span>

                    <span class="flex-1">
                        <span class="block text-ink group-hover:underline">{{ $dok['titel'] }}</span>
                        <span class="mt-0.5 block text-xs text-ink-soft">{{ $meta }}</span>
                    </span>

                    <span class="shrink-0 text-ink-soft">
                        <x-ui.icon name="arrow-right" :size="18" />
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
</section>
