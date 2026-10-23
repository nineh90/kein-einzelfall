@props([
    'seite' => null,   // die Seite, auf der der Baustein steht
    'auf' => 'cream',  // cream | card
])

@php
    use App\Models\GlossaryTerm;
    use App\Models\Language;

    $sprache = Language::aktuell();

    // Länger tippt niemand eine Abkürzung oder einen Begriff.
    $suche = mb_substr(trim((string) request()->query('suche', '')), 0, 100);

    $gruppen = $seite ? GlossaryTerm::nachBuchstaben($sprache->code, $seite) : collect();
    $ersatzsprache = null;

    /*
     * Sichtbarer Rückfall auf die Standardsprache, wie bei den Inhaltsseiten,
     * und hier aus einem zusätzlichen Grund: Die Begriffe stammen aus
     * deutschen Gesetzen und Bescheiden. Wer sie auf Englisch sucht und nichts
     * findet, braucht trotzdem den deutschen Begriff, denn genau der steht in
     * seinem Brief.
     */
    if ($seite && $gruppen->isEmpty() && $rueckfall = $sprache->fallback()) {
        $ersatz = GlossaryTerm::nachBuchstaben($rueckfall->code, $seite);

        if ($ersatz->isNotEmpty()) {
            $gruppen = $ersatz;
            $ersatzsprache = $rueckfall;
        }
    }

    $gesamt = $gruppen->flatten(1)->count();

    // „Behörden & Bescheide“ hat nur Begriffe, keine Abkürzungen (KEV-155).
    $mitAbkuerzungen = $gruppen->flatten(1)->contains(fn ($eintrag) => filled($eintrag->kuerzel));
    $passend = $gruppen->map(fn ($eintraege) => $eintraege->filter->passt($suche));
    $gefunden = $passend->flatten(1)->count();

    // Die Suche schickt an diese Seite zurück, in ihrer Sprache.
    $hier = request()->getPathInfo();
    $suchPfad = $sprache->pfad('/suche');
    $anker = fn (string $buchstabe) => 'buchstabe-'.($buchstabe === '#' ? 'zeichen' : Str::lower($buchstabe));
@endphp

{{--
    Glossar: die Einträge, die im Panel dieser Seite zugeordnet sind.

    Bis KEV-155 eine eigene Ansicht unter /glossar. Seit Taddi mehrere
    Glossare schickt („Abkürzungen Sozialrecht“, „Behörden & Bescheide“ …),
    ist es ein Baustein: Jede Seite mit diesem Baustein ist ein Glossar, und
    welcher Eintrag wo steht, wählt der Verein am Eintrag.

    Entscheidungen, die hier zählen:

    - Jeder Eintrag hat ein eigenes Sprungziel (`#gdb`). Ein Glossar, aus dem
      man nicht auf einen einzelnen Begriff verlinken kann, ist in einer Antwort
      des Vereins auf eine Anfrage nur die halbe Hilfe.
    - Die Buchstabenleiste ist ein <nav> mit echten Ankern und kommt ohne
      JavaScript aus.
    - Seit KEV-153 (rund 200 Abkürzungen) gibt es darüber ein Suchfeld. Es
      blendet nur aus, was nicht passt; leer zeigt es wieder alles, und die
      Suche des Browsers (Strg+F) findet weiterhin jeden Eintrag. Ohne
      JavaScript filtert der Server (?suche=), mit JavaScript die Seite
      schon beim Tippen (resources/js/glossar.js).
    - Kompakt wie ein Wörterbuch statt einer Karte je Eintrag: Bei 200
      Einträgen wäre die Seite sonst zehn Bildschirme lang.
--}}
<div @class(['px-4 md:px-8 py-8 lg:px-10 lg:py-12', 'bg-card border-y border-line' => $auf === 'card'])
     @if ($ersatzsprache) lang="{{ $ersatzsprache->code }}" dir="{{ $ersatzsprache->richtung }}" @endif>
    <div class="mx-auto max-w-6xl">

        @if ($gruppen->isEmpty())
            <p class="max-w-prose leading-relaxed text-ink-soft">
                {{ __('Hier entsteht ein Verzeichnis der Abkürzungen und Fachbegriffe. Es wird nach und nach ergänzt.') }}
            </p>
        @else

            {{-- Suche im Glossar. role="search", damit Vorlesehilfen sie
                 als Orientierungspunkt anbieten. Die Texte für das
                 Filtern beim Tippen stehen übersetzt in data-Attributen. --}}
            <form role="search" method="get" action="{{ $hier }}" class="mb-8 max-w-xl"
                  data-glossar-suche
                  data-gesamt="{{ $gesamt }}"
                  data-text-alle="{{ __(':gesamt Einträge') }}"
                  data-text-gefiltert="{{ __('Treffer: :anzahl von :gesamt') }}">
                <label for="glossar-suche" class="mb-2 block font-display text-lg text-ink">
                    {{ $mitAbkuerzungen ? __('Abkürzung oder Begriff suchen') : __('Begriff suchen') }}
                </label>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <input type="search"
                           id="glossar-suche"
                           name="suche"
                           value="{{ $suche }}"
                           maxlength="100"
                           autocomplete="off"
                           spellcheck="false"
                           aria-describedby="glossar-treffer"
                           class="w-full rounded-full border border-line bg-cream px-5 py-3 text-ink
                                  placeholder:text-ink-soft focus:border-green focus:outline-none">

                    <x-ui.button type="submit" variant="primary" class="shrink-0">{{ __('Suchen') }}</x-ui.button>
                </div>

                <p class="mt-2 text-sm text-ink-soft">
                    <span id="glossar-treffer" aria-live="polite" data-glossar-status>{{ $suche === ''
                        ? __(':gesamt Einträge', ['gesamt' => $gesamt])
                        : __('Treffer: :anzahl von :gesamt', ['anzahl' => $gefunden, 'gesamt' => $gesamt]) }}</span>
                    <a href="{{ $hier }}" data-glossar-zuruecksetzen @if ($suche === '') hidden @endif
                       class="ms-2 text-green-deep underline">{{ __('Alle zeigen') }}</a>
                </p>
            </form>

            {{-- Nichts gefunden: keine Sackgasse, sondern der Weg in die
                 Suche der ganzen Website. --}}
            <div data-glossar-leer @if ($suche === '' || $gefunden > 0) hidden @endif
                 class="mb-10 max-w-prose leading-relaxed text-ink-soft">
                <p>{{ __('Dazu steht noch nichts im Glossar.') }}</p>
                <p class="mt-2">
                    <a href="{{ $suchPfad }}?q={{ urlencode($suche) }}" data-glossar-websuche
                       data-basis="{{ $suchPfad }}?q="
                       class="text-green-deep underline">{{ __('Auf der ganzen Website suchen') }}</a>
                </p>
            </div>

            {{-- Buchstabenleiste. Nur die Buchstaben, zu denen es auch
                 etwas gibt: Ein ausgegrautes „Q“ nimmt Platz weg und sagt
                 nichts, was die Liste darunter nicht auch sagt. --}}
            <nav aria-label="{{ __('Nach Anfangsbuchstaben springen') }}" class="mb-10">
                <ul class="flex flex-wrap gap-1.5">
                    @foreach ($gruppen->keys() as $buchstabe)
                        <li data-glossar-sprung="{{ $anker($buchstabe) }}" @if ($passend[$buchstabe]->isEmpty()) hidden @endif>
                            <a href="#{{ $anker($buchstabe) }}"
                               class="inline-flex h-9 w-9 items-center justify-center rounded-full
                                      border border-line font-display text-sm text-ink no-underline
                                      hover:border-green hover:bg-green hover:text-on-green">
                                {{ $buchstabe }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            @foreach ($gruppen as $buchstabe => $eintraege)
                <section class="mb-10" aria-labelledby="{{ $anker($buchstabe) }}"
                         data-glossar-abschnitt @if ($passend[$buchstabe]->isEmpty()) hidden @endif>

                    {{-- scroll-mt hält die Überschrift beim Anspringen
                         unter dem klebenden Kopfbereich sichtbar. --}}
                    <h2 id="{{ $anker($buchstabe) }}"
                        class="mb-2 scroll-mt-24 border-b border-line pb-2 font-display text-xl
                               font-medium text-green-deep">
                        {{ $buchstabe }}
                    </h2>

                    {{-- <dl>: Abkürzung und Auflösung sind ein Paar, und
                         Vorlesehilfen sagen das auch so an. Auf breiten
                         Bildschirmen in zwei Spalten wie ein Wörterbuch,
                         von oben nach unten zu lesen. --}}
                    <dl class="md:columns-2 md:gap-10">
                        @foreach ($eintraege as $eintrag)
                            <div id="{{ $eintrag->slug }}"
                                 data-glossar-eintrag
                                 data-kuerzel="{{ GlossaryTerm::kompakt((string) $eintrag->kuerzel) }}"
                                 data-suchtext="{{ $eintrag->suchtext() }}"
                                 @unless ($eintrag->passt($suche)) hidden @endunless
                                 class="grid scroll-mt-24 break-inside-avoid grid-cols-[5.5rem_1fr] gap-x-4 sm:grid-cols-[6.5rem_1fr]
                                        border-b border-line py-2.5 target:bg-green-mist">

                                @if ($eintrag->kuerzel)
                                    {{-- <abbr> mit title: Wer mit der Maus darauf
                                         zeigt, sieht die Auflösung. Sichtbar steht
                                         sie trotzdem daneben — ein title allein ist
                                         auf Tastatur und Touch nicht erreichbar. --}}
                                    <dt class="break-words font-display font-semibold text-ink">
                                        <abbr title="{{ $eintrag->begriff }}" class="no-underline">{{ $eintrag->kuerzel }}</abbr>
                                    </dt>
                                    {{-- Silbentrennung: „Arbeitsunfähigkeitsbescheinigung“
                                         bricht auf dem Handy sonst mitten im Wort. --}}
                                    <dd class="hyphens-auto text-ink">{{ $eintrag->begriff }}</dd>
                                @else
                                    <dt class="col-span-2 font-display font-semibold text-ink">{{ $eintrag->begriff }}</dt>
                                @endif

                                @if ($eintrag->erklaerung || $eintrag->mehr_url)
                                    <dd @class([
                                        'mt-1 hyphens-auto text-[0.9375rem] leading-relaxed text-ink-soft',
                                        'col-start-2' => $eintrag->kuerzel,
                                        'col-span-2' => ! $eintrag->kuerzel,
                                    ])>
                                        {{ $eintrag->erklaerung }}

                                        @if ($eintrag->mehr_url)
                                            <a href="{{ $eintrag->mehr_url }}"
                                               class="mt-1 inline-block text-green-deep underline">
                                                {{ $eintrag->mehr_label ?: __('Mehr dazu') }}
                                                <span class="sr-only">{{ __('zu „:begriff“', ['begriff' => $eintrag->ueberschrift()]) }}</span>
                                            </a>
                                        @endif
                                    </dd>
                                @endif
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endforeach
        @endif
    </div>
</div>
