@props([
    'eyebrow' => null,
    'titel' => 'Jetzt spenden',
    'text' => null,        // Einleitung — auf der Startseite: warum spenden
    'bank' => null,        // ['institut'=>, 'iban'=>, 'bic'=>, 'empfaenger'=>]
    'paypal' => null,      // ['empfaenger'=>, 'url'=>]
    'projekte' => [],      // [['titel'=>, 'widget'=>, 'url'=>], ...]
    'bescheinigung' => null,
    'mehr' => null,        // ['label'=>, 'url'=>] — Verweis auf alle Spendenmöglichkeiten
    /*
     * Beide Fassungen stehen seit KEV-25 im selben Raster wie die übrigen
     * Bausteine (max-w-6xl): links Überschrift und Einleitung, rechts Konto
     * und PayPal in einer gemeinsamen Karte.
     *
     * Kompakt = die Fassung für die Startseite: Die betterplace-Projekte
     * bleiben, falls gepflegt, in der rechten Spalte. Ohne „kompakt“ — die
     * Spendenseite selbst — bekommen sie die volle Breite darunter, zwei
     * nebeneinander. Die eingebetteten Rahmen brauchen den Platz, und so
     * steht auf dem Desktop keine lange schmale Kastenreihe mehr.
     */
    'kompakt' => false,
    'auf' => 'cream',      // cream | card
])

@php
    // Kästen stehen auf der jeweils anderen Fläche, sonst verschwämmen sie
    // auf der Karte mit dem Hintergrund.
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';

    // Ein Verweis ohne Beschriftung oder Ziel — siehe knoepfe() in helpers.php.
    $mehr = knoepfe([$mehr])[0] ?? null;

    $projekteBreit = $projekte && ! $kompakt;
@endphp

{{-- id="spenden": Sprungziel für Kopf, Band oder geteilte Links („…/#spenden“). --}}
<section id="spenden" @class([
    'scroll-mt-24 px-4 py-10 lg:px-10 lg:py-16',
    'bg-card border-y border-line' => $auf === 'card',
]) aria-labelledby="spenden-titel">
    <div class="mx-auto max-w-6xl">

        {{-- Die Reihenfolge im Quelltext ist die für Mobil und Vorlesehilfen:
             Einleitung, Möglichkeiten, Projekte, Bescheinigung. Auf dem Desktop
             rückt die Bescheinigung per Rasterplatz unter die Einleitung — dort
             ist sonst nur leere Fläche. Die zweite Zeile ist 1fr, damit die hohe
             rechte Spalte nicht die erste Zeile streckt und die Bescheinigung
             nach unten schiebt. Zwischen lg und xl bekommt die rechte Spalte
             mehr Anteil, sonst bricht dort die IBAN neben dem QR-Code um. --}}
        <div class="grid gap-8 lg:grid-cols-[minmax(0,4fr)_minmax(0,8fr)] lg:grid-rows-[auto_1fr_auto] lg:gap-x-10 lg:gap-y-10 xl:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] xl:gap-x-16">

            <div class="lg:col-start-1 lg:row-start-1">
                @if ($eyebrow)
                    <x-ui.eyebrow class="mb-3">{{ $eyebrow }}</x-ui.eyebrow>
                @endif

                {{-- Derselbe kurze Strich wie über jeder Abschnittsüberschrift. --}}
                <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
                <h2 id="spenden-titel" class="mb-4 font-display text-2xl font-medium text-ink lg:text-3xl">
                    {{ $titel }}
                </h2>

                @if ($text)
                    <p class="max-w-prose leading-relaxed text-ink-soft">{{ $text }}</p>
                @endif

                @if ($mehr)
                    {{-- Ein Link, kein Knopf: Die Handlung auf dieser Fläche ist das
                         Spenden selbst; der Weg zur vollständigen Seite (betterplace,
                         Spendenbescheinigung) ist der Nebenweg. --}}
                    <p class="mt-5">
                        <a href="{{ $mehr['url'] }}" class="text-green-deep underline">{{ $mehr['label'] }}<x-ui.icon
                            name="arrow-right" :size="16" class="ml-1.5 inline align-[-2px]" /></a>
                    </p>
                @endif
            </div>

            <div class="flex flex-col gap-4 lg:col-start-2 lg:row-span-2 lg:row-start-1">

            @if ($bank || $paypal)
                {{-- Konto und PayPal in einer Karte, getrennt durch eine Linie:
                     Es sind zwei Wege zum selben Ziel, keine zwei Themen. --}}
                <div class="overflow-hidden rounded-card border border-line {{ $innen }}">

                @if ($bank)
                    @php
                        /*
                         * QR-Code für die Überweisung („Girocode“). Fast jede
                         * Banking-App in Deutschland liest ihn und füllt damit das
                         * Formular aus — eine IBAN abzutippen ist fehleranfällig,
                         * und die 22 Stellen sind für Menschen mit Konzentrations-
                         * oder Sehschwierigkeiten eine echte Hürde.
                         *
                         * Er ergänzt die Angaben und ersetzt sie nicht: Wer keine
                         * Kamera, keine App oder kein Smartphone hat, muss
                         * genauso weit kommen.
                         */
                        $empfaenger = $bank['empfaenger'] ?? 'KE!N EINZELFALL e.V.';
                        $girocode = \App\Support\Girocode::svg(
                            $empfaenger,
                            $bank['iban'] ?? '',
                            $bank['bic'] ?? null,
                            $bank['verwendungszweck'] ?? null,
                        );
                    @endphp

                    <div class="grid gap-6 p-5 sm:grid-cols-[minmax(0,1fr)_auto] sm:p-7">
                        <div>
                            <h3 class="mb-4 font-display text-lg text-ink">
                                {{ __('rahmen.spenden.ueberweisung') }}
                                @if (!empty($bank['institut']))
                                    <span class="font-sans text-sm font-normal text-ink-soft">
                                        ({{ $bank['institut'] }})
                                    </span>
                                @endif
                            </h3>

                            {{-- IBAN in einer <dl>: Screenreader lesen Bezeichnung und Wert
                                 als Paar. Die Ziffern in Vierergruppen und mit
                                 font-variant-numeric: tabular-nums sind beim Abtippen
                                 deutlich leichter zu verfolgen. --}}
                            <dl class="grid grid-cols-[auto_minmax(0,1fr)] items-baseline gap-x-5 gap-y-2 text-sm">
                                <dt class="text-ink-soft">{{ __('rahmen.spenden.empfaenger') }}</dt>
                                <dd class="text-base text-ink">{{ $empfaenger }}</dd>

                                <dt class="text-ink-soft">IBAN</dt>
                                <dd class="font-mono text-base tracking-wide text-ink [font-variant-numeric:tabular-nums]">
                                    {{ $bank['iban'] }}
                                </dd>

                                @if (!empty($bank['bic']))
                                    <dt class="text-ink-soft">BIC</dt>
                                    <dd class="font-mono text-base text-ink">{{ $bank['bic'] }}</dd>
                                @endif
                            </dl>

                            {{-- Kopieren gibt es nur mit JavaScript und Zwischenablage
                                 (kopieren.js nimmt das hidden weg). Ohne beides bliebe
                                 ein Knopf, der nichts tut. Kopiert wird ohne
                                 Leerzeichen: Das nehmen alle Banking-Formulare an. --}}
                            @if (!empty($bank['iban']))
                                <div class="mt-5 flex flex-wrap items-center gap-3" data-kopieren-bereich hidden>
                                    <button type="button"
                                            data-kopieren="{{ str_replace(' ', '', $bank['iban']) }}"
                                            class="inline-flex items-center gap-2 rounded-full border border-line px-4 py-1.5
                                                   text-sm text-green-deep transition-colors hover:border-green hover:bg-green-mist">
                                        <x-ui.icon name="copy" :size="16" />
                                        {{ __('rahmen.spenden.iban_kopieren') }}
                                    </button>
                                    <span role="status" data-kopieren-status
                                          data-text="{{ __('rahmen.spenden.iban_kopiert') }}"
                                          class="inline-flex items-center gap-1 text-sm text-green-deep"></span>
                                </div>
                            @endif
                        </div>

                        @if ($girocode)
                            {{-- Der Code selbst ist für eine Vorlesehilfe nichts als
                                 eine Fläche. Was er ist und wofür er gut ist, steht
                                 deshalb sichtbar darunter — und damit gleich für alle. --}}
                            <figure class="w-40 sm:w-36">
                                {{-- Der einzige Ort auf dieser Seite mit fest
                                     eingebauten Farben, und das mit Absicht: Ein
                                     QR-Code ist kein Text, sondern etwas, das
                                     eine Kamera lesen muss. Dunkelmodus,
                                     Monochrom oder invertierte Farben machen ihn
                                     für manche Scanner unbrauchbar. Die
                                     Darstellungs-Einstellungen greifen deshalb
                                     hier bewusst nicht — die Angaben daneben
                                     sind der Weg, der für alle funktioniert. --}}
                                <div class="girocode-flaeche rounded-lg border border-line p-2.5"
                                     role="img"
                                     aria-label="{{ __('rahmen.spenden.qr_label') }}">
                                    {!! $girocode !!}
                                </div>
                                <figcaption class="mt-2 text-xs leading-snug text-ink-soft">
                                    {{ __('rahmen.spenden.qr_hinweis') }}
                                </figcaption>
                            </figure>
                        @endif
                    </div>
                @endif

                @if ($paypal)
                    <div @class([
                        'flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-7',
                        'border-t border-line' => $bank,
                    ])>
                        <div>
                            <h3 class="font-display text-lg text-ink">PayPal</h3>
                            @if (!empty($paypal['empfaenger']))
                                <p class="mt-1 text-sm text-ink-soft">
                                    {{ __('rahmen.spenden.empfaenger') }}: {{ $paypal['empfaenger'] }}
                                </p>
                            @endif
                        </div>
                        {{-- Reiner Link, kein eingebettetes Skript: Solange niemand
                             klickt, erfährt PayPal nichts von diesem Besuch.
                             Ohne Adresse bleibt der Empfänger stehen — ein
                             Knopf ins Leere wäre schlimmer als keiner. --}}
                        @if (!empty($paypal['url']))
                            <x-ui.button :href="$paypal['url']" variant="primary" size="sm"
                                         class="self-start sm:self-auto"
                                         target="_blank" rel="noopener noreferrer">
                                {{ __('rahmen.spenden.paypal_knopf') }}
                                <x-ui.icon name="external" :size="16" />
                                <span class="sr-only">{{ __('rahmen.neuer_tab') }}</span>
                            </x-ui.button>
                        @endif
                    </div>
                @endif

                </div>
            @endif

            @if ($projekte && $kompakt)
                <div class="rounded-card border border-line {{ $innen }} px-5 py-5">
                    <h3 class="mb-1 font-display text-lg text-ink">{{ __('rahmen.spenden.projekte') }}</h3>
                    <p class="mb-4 text-sm text-ink-soft">
                        {{ __('rahmen.spenden.projekte_hinweis') }}
                    </p>

                    <div class="flex flex-col gap-3">
                        @foreach ($projekte as $projekt)
                            <x-blocks.embed
                                :titel="$projekt['titel']"
                                anbieter="betterplace.org"
                                :src="$projekt['widget']"
                                :direktlink="$projekt['url'] ?? null"
                                :hoehe="330" />
                        @endforeach
                    </div>
                </div>
            @endif

            </div>

            @if ($projekteBreit)
                <div class="lg:col-span-2 lg:row-start-3 lg:border-t lg:border-line lg:pt-10">
                    <h3 class="mb-1 font-display text-xl text-ink">{{ __('rahmen.spenden.projekte') }}</h3>
                    <p class="mb-5 text-sm text-ink-soft">
                        {{ __('rahmen.spenden.projekte_hinweis') }}
                    </p>

                    {{-- items-start: Klappt eine Einbettung auf, wächst nur sie —
                         die daneben bleibt, wie sie ist. --}}
                    <div class="grid items-start gap-4 md:grid-cols-2">
                        @foreach ($projekte as $projekt)
                            <x-blocks.embed
                                :titel="$projekt['titel']"
                                anbieter="betterplace.org"
                                :src="$projekt['widget']"
                                :direktlink="$projekt['url'] ?? null"
                                :hoehe="330" />
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($bescheinigung)
                {{-- Kein Kasten, sondern eine Randnotiz: Das ist keine Art zu
                     spenden, sondern eine Auskunft danach. --}}
                <div class="border-l-2 border-green-brand pl-5 lg:col-start-1 lg:row-start-2 lg:self-start">
                    <h3 class="mb-2 font-display text-lg text-ink">{{ __('rahmen.spenden.bescheinigung') }}</h3>
                    {{-- ?? '', falls im Panel nur die E-Mail gepflegt wurde:
                         der leere Text wird beim Speichern entfernt. --}}
                    <p class="max-w-prose text-sm leading-relaxed text-ink-soft">{{ $bescheinigung['text'] ?? '' }}</p>
                    @if (!empty($bescheinigung['email']))
                        <p class="mt-3">
                            <a href="mailto:{{ $bescheinigung['email'] }}"
                               class="text-green-deep underline">{{ $bescheinigung['email'] }}</a>
                        </p>
                    @endif
                </div>
            @endif

        </div>
    </div>
</section>
