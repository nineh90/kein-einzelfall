@props([
    'titel' => null,
    'dokumente' => [],   // [['titel' =>, 'url' =>, 'bytes' =>, 'typ' =>, 'quelle' =>], ...]
    'auf' => 'cream',    // cream | card
])

@php
    // Kästen stehen auf der jeweils anderen Fläche, sonst verschwämmen sie
    // auf der Karte mit dem Hintergrund.
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';
@endphp

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

    /*
     * Fremde Adresse oder eigene Datei?
     *
     * Das ist keine Kosmetik, sondern die Umsetzung einer Entscheidung aus der
     * Besprechung vom 02.08.2026: Behoerdenformulare werden nicht mehr selbst
     * gehostet, sondern verlinkt — Aemter aendern ihre Vordrucke, und eine
     * Kopie bei uns waere irgendwann die falsche Fassung. Wer einen veralteten
     * Antrag einreicht, verliert Zeit, die er oft nicht hat.
     *
     * Ein Verweis nach draussen muss aber als solcher erkennbar sein, bevor
     * jemand ihn antippt (WCAG 3.2.5): Er verlaesst unsere Seite, wir haben
     * keinen Einfluss auf das, was dort passiert, und er tut auch nicht das,
     * was ein Download-Symbol verspricht.
     */
    $istExtern = fn (array $d): bool => (bool) preg_match('#^https?://#i', $d['url'] ?? '');
@endphp

{{--
    Download-Liste.

    Barrierefreiheits-Punkte, die hier zählen:
    - Der Linktext ist der Dokumenttitel, nie der Dateiname. "6.5.3.1.-Info-
      Erwerbsminderung-Deine-Zweigstelle.pdf" vorgelesen zu bekommen ist zumutbar
      für niemanden.
    - Dateityp und Größe stehen im Linktext (WCAG 3.2.5): wer über Mobilfunk liest,
      soll vor dem Tippen wissen, was auf ihn zukommt.
    - Bei fremden Adressen steht stattdessen die Herkunft dort — sie ist die
      Angabe, die vor dem Tippen zählt.
    - Kein target="_blank": ungefragte neue Tabs sind desorientierend. Wer will,
      öffnet selbst in einem neuen Tab.
--}}
{{-- Derselbe Rahmen wie die Textbausteine: Ohne Container lief die Liste über
     die volle Fensterbreite und fiel aus dem Satzspiegel der Seite. --}}
<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])
         @if ($titel) aria-labelledby="dl-{{ Str::slug($titel) }}" @else aria-label="{{ __('rahmen.dokumente.bereich') }}" @endif>
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
            <ul class="flex flex-col divide-y divide-line overflow-hidden rounded-card border border-line {{ $innen }}">
        @foreach ($dokumente as $dok)
            @php
                $extern = $istExtern($dok);

                if ($extern) {
                    // Die Herkunft steht so da, wie sie in der Adresszeile
                    // erscheint. Ein gepflegter Name („Bundesagentur für Arbeit“)
                    // hat Vorrang — nur muss er dann auch stimmen, deshalb ist
                    // die Adresse der Rückfall und nicht umgekehrt.
                    $herkunft = $dok['quelle'] ?? preg_replace(
                        '/^www\./', '', parse_url($dok['url'], PHP_URL_HOST) ?: ''
                    );
                    $meta = __('rahmen.dokumente.extern', ['quelle' => $herkunft]);
                } else {
                    $typ = strtoupper($dok['typ'] ?? pathinfo($dok['url'], PATHINFO_EXTENSION) ?: 'PDF');
                    $gr = $groesse($dok['bytes'] ?? null);
                    // Zusammengesetzt in PHP: eine @if-Direktive direkt an Text geklebt
                    // ("...Datei@if") erkennt Blade nicht als Direktive.
                    $meta = $gr ? "{$typ}-Datei, {$gr}" : "{$typ}-Datei";
                }
            @endphp
            <li>
                {{-- download und rel="noreferrer" schliessen sich gegenseitig aus:
                     Das eine gilt nur für eigene Dateien, das andere nur für
                     fremde Ziele. --}}
                <a href="{{ $dok['url'] }}"
                   @if ($extern) rel="noreferrer noopener" @else download @endif
                   class="group flex items-center gap-4 px-4 py-3.5 no-underline hover:bg-green-mist">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg
                                 bg-green-mist font-display text-[0.625rem] font-semibold text-green-deep
                                 group-hover:bg-cream">
                        @if ($extern)
                            <x-ui.icon name="external" :size="18" />
                        @else
                            {{ $typ }}
                        @endif
                    </span>

                    <span class="flex-1">
                        <span class="block text-[0.9375rem] text-ink group-hover:underline">{{ $dok['titel'] }}</span>
                        <span class="mt-0.5 block text-xs text-ink-soft">{{ $meta }}</span>
                    </span>

                    <span class="shrink-0 text-ink-soft transition-transform group-hover:translate-x-0.5">
                        <x-ui.icon :name="$extern ? 'external' : 'arrow-right'" :size="18" />
                    </span>
                </a>
            </li>
                @endforeach
            </ul>

            {{-- Ein Satz unter der Liste statt eines Hinweises je Eintrag: Die
                 Begründung gilt für alle fremden Verweise gleichermaßen, und in
                 jeder Zeile wiederholt wäre sie nur Rauschen. --}}
            @if (collect($dokumente)->contains($istExtern))
                <p class="mt-3 text-sm text-ink-soft">{{ __('rahmen.dokumente.warum_extern') }}</p>
            @endif
        </div>
    </div>
</section>
