@props([
    'titel' => null,
    'dokumente' => [],   // [['titel' =>, 'datei' =>, 'url' =>, 'bytes' =>, 'typ' =>, 'quelle' =>], ...]
    'auf' => 'cream',    // cream | card
    // Ohne eigenes Band und Satzspiegel, als Spalte neben einem Text
    // (x-blocks.text-mit-dokumenten, KEV-226).
    'rahmen' => true,
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
        // Im Panel hochgeladen (`datei`, seit 21.10.2026) geht vor der
        // eingetragenen Adresse. Die Größe ergibt sich dann aus der Datei,
        // niemand muss sie mehr in Bytes eintippen.
        ->map(function ($d) {
            if (filled($d['datei'] ?? null)) {
                $d['url'] = $d['datei'];
            }

            $datei = \App\Support\Medien::dateipfad($d['url'] ?? null);
            if (blank($d['bytes'] ?? null) && $datei !== null && is_file($datei)) {
                $d['bytes'] = filesize($datei);
            }

            return $d;
        })
        ->filter(function ($d) {
            $url = $d['url'] ?? '';
            $eigen = str_starts_with($url, '/'.\App\Support\Dokument::ORDNER.'/') || str_starts_with($url, '/storage/');

            return $url !== '' && (! $eigen || \App\Support\Medien::vorhanden($url));
        })
        ->values()
        ->all();

    // Dezimal- und Tausendertrennzeichen nach Sprache: „1,2 MB“, auf
    // Englisch „1.2 MB“.
    $englisch = app()->getLocale() === 'en';
    $groesse = function (?int $b) use ($englisch): string {
        if (! $b) return '';
        [$komma, $punkt] = $englisch ? ['.', ','] : [',', '.'];
        return $b >= 1048576
            ? number_format($b / 1048576, 1, $komma, $punkt).' MB'
            : number_format($b / 1024, 0, $komma, $punkt).' KB';
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

    // Ab acht Einträgen zwei Spalten, nur als eigenes Band (nicht neben Text).
    $zweispaltig = $rahmen && count($dokumente) >= 8;
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
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12' => $rahmen,
    'bg-card border-y border-line' => $rahmen && $auf === 'card',
])
         @if ($titel) aria-labelledby="dl-{{ Str::slug($titel) }}" @else aria-label="{{ __('rahmen.dokumente.bereich') }}" @endif>
    <div @class(['mx-auto max-w-6xl' => $rahmen])>
        {{-- Lange Listen (Atteste: 27) auf großen Bildschirmen in zwei
             Spalten (KEV-245), sonst bliebe rechts die halbe Seite leer. --}}
        <div @class(['max-w-prose' => $rahmen && ! $zweispaltig])>
            @if ($titel)
                <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
                <h2 id="dl-{{ Str::slug($titel) }}" @class([
                    'mb-4 font-display font-medium text-green',
                    'text-2xl lg:text-3xl' => $rahmen,
                    'text-xl lg:text-2xl' => ! $rahmen,
                ])>
                    {{ $titel }}
                </h2>
            @endif

            {{-- Als abgesetzte Karte statt randloser Liste — sonst wirkt der
                 Abschnitt wie ein loses Anhängsel unter dem Fließtext. --}}
            <ul @class([
                'overflow-hidden rounded-card border border-line', $innen,
                'flex flex-col divide-y divide-line' => ! $zweispaltig,
                // Trennlinien an den Einträgen statt als Fugen: Bei ungerader
                // Zahl bliebe sonst die letzte Zelle als graue Fläche stehen.
                // -mb-px schiebt die Unterkante der letzten Reihe unter den
                // Rahmen der Liste.
                'grid md:grid-cols-2 [&>li]:-mb-px [&>li]:border-b [&>li]:border-line md:[&>li:nth-child(odd)]:border-e' => $zweispaltig,
            ])>
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
                    $meta = $gr
                        ? __(':typ-Datei, :groesse', ['typ' => $typ, 'groesse' => $gr])
                        : __(':typ-Datei', ['typ' => $typ]);
                    // PDFs zeigt der Browser selbst an, andere Formate nicht.
                    if ($typ === 'PDF') {
                        $meta .= ', '.__('rahmen.dokumente.ansehen');
                    }
                }
            @endphp
            <li class="flex items-stretch">
                {{-- Eigene Dateien öffnen im Browser (KEV-245, Taddi: erst
                     ansehen, dann entscheiden), daneben ein eigener Knopf zum
                     Herunterladen. Fremde Adressen nur als Verweis mit
                     rel="noreferrer". --}}
                <a href="{{ $dok['url'] }}"
                   @if ($extern) rel="noreferrer noopener" @endif
                   class="group flex min-w-0 flex-1 items-center gap-4 px-4 py-3.5 no-underline hover:bg-green-mist">
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

                    @if ($extern)
                        <span class="shrink-0 text-ink-soft transition-transform group-hover:translate-x-0.5">
                            <x-ui.icon name="external" :size="18" />
                        </span>
                    @endif
                </a>

                @unless ($extern)
                    <a href="{{ $dok['url'] }}" download
                       class="flex shrink-0 items-center gap-2 border-s border-line px-4 text-sm text-green-deep
                              no-underline hover:bg-green-mist hover:underline">
                        <x-ui.icon name="download" :size="18" />
                        <span class="hidden sm:inline">{{ __('rahmen.dokumente.herunterladen') }}</span>
                        <span class="sr-only sm:hidden">{{ __('rahmen.dokumente.herunterladen') }}</span>
                        <span class="sr-only">: {{ $dok['titel'] }}</span>
                    </a>
                @endunless
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
