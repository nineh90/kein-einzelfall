@props([
    'titel' => null,
    'dokumente' => [],   // [['titel' =>, 'url' =>, 'bytes' =>, 'typ' =>], ...]
])

@php
    /*
     * Eintraege ohne vorhandene Datei fliegen raus.
     *
     * Ein Download-Knopf, der ins Leere fuehrt, ist fuer diese Zielgruppe
     * schlechter als kein Knopf: Wer nach einer Straftat ein Formular sucht und
     * auf einen 404 laeuft, sucht kein zweites Mal. Externe Adressen bleiben
     * unangetastet — die koennen wir nicht pruefen.
     */
    $dokumente = collect($dokumente)
        ->filter(function ($d) {
            $url = $d['url'] ?? '';

            return $url !== ''
                && (! str_starts_with($url, '/'.\App\Support\Dokument::ORDNER.'/')
                    || is_file(public_path(ltrim($url, '/'))));
        })
        ->values()
        ->all();

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
{{-- Derselbe Rahmen wie die Textbausteine: Ohne Container lief die Liste über
     die volle Fensterbreite und fiel aus dem Satzspiegel der Seite. --}}
<section class="px-4 py-8 lg:px-10 lg:py-12"
         @if ($titel) aria-labelledby="dl-{{ Str::slug($titel) }}" @else aria-label="Dokumente zum Herunterladen" @endif>
    <div class="mx-auto max-w-6xl">
        <div class="max-w-prose">
            @if ($titel)
                <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
                <h2 id="dl-{{ Str::slug($titel) }}" class="mb-4 font-display text-2xl font-medium text-ink lg:text-3xl">
                    {{ $titel }}
                </h2>
            @endif

            {{-- Als abgesetzte Karte statt randloser Liste — sonst wirkt der
                 Abschnitt wie ein loses Anhängsel unter dem Fließtext. --}}
            <ul class="flex flex-col divide-y divide-line overflow-hidden rounded-card border border-line bg-card">
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
                   class="group flex items-center gap-4 px-4 py-3.5 no-underline hover:bg-green-mist">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg
                                 bg-green-mist font-display text-[0.625rem] font-semibold text-green-deep
                                 group-hover:bg-cream">
                        {{ $typ }}
                    </span>

                    <span class="flex-1">
                        <span class="block text-[0.9375rem] text-ink group-hover:underline">{{ $dok['titel'] }}</span>
                        <span class="mt-0.5 block text-xs text-ink-soft">{{ $meta }}</span>
                    </span>

                    <span class="shrink-0 text-ink-soft transition-transform group-hover:translate-x-0.5">
                        <x-ui.icon name="arrow-right" :size="18" />
                    </span>
                </a>
            </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
