@props([
    'eyebrow' => null,
    'titel' => 'Jetzt spenden',
    'text' => null,        // Einleitung — auf der Startseite: warum spenden
    'bank' => null,        // ['institut'=>, 'iban'=>, 'bic'=>]
    'paypal' => null,      // ['empfaenger'=>, 'url'=>]
    'projekte' => [],      // [['titel'=>, 'widget'=>, 'url'=>], ...]
    'bescheinigung' => null,
    'mehr' => null,        // ['label'=>, 'url'=>] — Verweis auf alle Spendenmöglichkeiten
    /*
     * Kompakt = die Fassung für die Startseite: Einleitung links, die
     * Möglichkeiten rechts daneben, volle Seitenbreite wie die übrigen
     * Bausteine dort. Ohne „kompakt“ stehen die Kästen untereinander in einer
     * Textspalte — die Fassung für die Spendenseite selbst.
     */
    'kompakt' => false,
    'auf' => 'cream',      // cream | card
])

@php
    // Kästen stehen auf der jeweils anderen Fläche, sonst verschwämmen sie
    // auf der Karte mit dem Hintergrund.
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';
@endphp

@php
    // Ein Verweis ohne Beschriftung oder Ziel — siehe knoepfe() in helpers.php.
    $mehr = knoepfe([$mehr])[0] ?? null;
@endphp

{{-- id="spenden": Sprungziel für Kopf, Band oder geteilte Links („…/#spenden“). --}}
<section id="spenden" @class([
    'scroll-mt-24 px-4 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
]) aria-labelledby="spenden-titel">
    <div @class(['mx-auto', 'max-w-6xl' => $kompakt, 'max-w-3xl' => ! $kompakt])>

        <div @class(['grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:gap-12' => $kompakt])>

            <div>
                @if ($eyebrow)
                    <x-ui.eyebrow class="mb-3">{{ $eyebrow }}</x-ui.eyebrow>
                @endif

                {{-- Derselbe kurze Strich wie über jeder Abschnittsüberschrift. --}}
                <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
                <h2 id="spenden-titel" @class([
                    'font-display font-medium text-ink',
                    'mb-4 text-2xl lg:text-3xl' => $kompakt,
                    'mb-6 text-2xl' => ! $kompakt,
                ])>
                    {{ $titel }}
                </h2>

                @if ($text)
                    <p class="max-w-prose leading-relaxed text-ink-soft">{{ $text }}</p>
                @endif

                @if ($mehr)
                    {{-- Ein Link, kein Knopf: Die Handlung auf dieser Fläche ist das
                         Spenden selbst; der Weg zur vollständigen Seite (betterplace,
                         Spendenbescheinigung) ist der Nebenweg. --}}
                    <p class="mt-4">
                        <a href="{{ $mehr['url'] }}" class="text-green-deep underline">{{ $mehr['label'] }}</a>
                    </p>
                @endif
            </div>

            <div class="flex flex-col gap-4">

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
                    $girocode = \App\Support\Girocode::svg(
                        $bank['empfaenger'] ?? 'KE!N EINZELFALL e.V.',
                        $bank['iban'] ?? '',
                        $bank['bic'] ?? null,
                        $bank['verwendungszweck'] ?? null,
                    );
                @endphp

                <div class="rounded-card border border-line {{ $innen }} px-5 py-5">
                    <h3 class="mb-3 font-display text-lg text-ink">
                        {{ __('rahmen.spenden.ueberweisung') }}
                        @if (!empty($bank['institut']))
                            <span class="font-sans text-sm font-normal text-ink-soft">
                                ({{ $bank['institut'] }})
                            </span>
                        @endif
                    </h3>

                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        {{-- IBAN in einer <dl>: Screenreader lesen Bezeichnung und Wert
                             als Paar. Die Ziffern in Vierergruppen und mit
                             font-variant-numeric: tabular-nums sind beim Abtippen
                             deutlich leichter zu verfolgen. --}}
                        <dl class="flex flex-col gap-2 text-sm">
                            <div class="flex flex-wrap gap-x-3">
                                <dt class="w-16 shrink-0 text-ink-soft">IBAN</dt>
                                <dd class="font-mono text-base tracking-wide text-ink [font-variant-numeric:tabular-nums]">
                                    {{ $bank['iban'] }}
                                </dd>
                            </div>
                            @if (!empty($bank['bic']))
                                <div class="flex flex-wrap gap-x-3">
                                    <dt class="w-16 shrink-0 text-ink-soft">BIC</dt>
                                    <dd class="font-mono text-base text-ink">{{ $bank['bic'] }}</dd>
                                </div>
                            @endif
                        </dl>

                        @if ($girocode)
                            {{-- Als <figure> mit Bildunterschrift statt als nacktes
                                 Bild: Der Code selbst ist für eine Vorlesehilfe
                                 nichts als eine Fläche. Was er ist und wofür er
                                 gut ist, muss danebenstehen — und steht damit
                                 gleich für alle da. --}}
                            <figure class="w-40 shrink-0">
                                {{-- Der einzige Ort auf dieser Seite mit fest
                                     eingebauten Farben, und das mit Absicht: Ein
                                     QR-Code ist kein Text, sondern etwas, das
                                     eine Kamera lesen muss. Dunkelmodus,
                                     Monochrom oder invertierte Farben machen ihn
                                     für manche Scanner unbrauchbar. Die
                                     Darstellungs-Einstellungen greifen deshalb
                                     hier bewusst nicht — die Angaben daneben
                                     sind der Weg, der für alle funktioniert. --}}
                                <div class="girocode-flaeche rounded-lg border border-line p-3"
                                     role="img"
                                     aria-label="{{ __('rahmen.spenden.qr_label') }}">
                                    {!! $girocode !!}
                                </div>
                                <figcaption class="mt-2 text-xs text-ink-soft">
                                    {{ __('rahmen.spenden.qr_hinweis') }}
                                </figcaption>
                            </figure>
                        @endif
                    </div>
                </div>
            @endif

            @if ($paypal)
                <div class="rounded-card border border-line {{ $innen }} px-5 py-5">
                    <h3 class="mb-2 font-display text-lg text-ink">PayPal</h3>
                    @if (!empty($paypal['empfaenger']))
                        <p class="mb-4 text-sm text-ink-soft">
                            {{ __('rahmen.spenden.empfaenger') }}: {{ $paypal['empfaenger'] }}
                        </p>
                    @endif
                    {{-- Reiner Link, kein eingebettetes Skript: Solange niemand
                         klickt, erfährt PayPal nichts von diesem Besuch.
                         Ohne Adresse bleibt der Empfänger darüber stehen — ein
                         Knopf ins Leere wäre schlimmer als keiner. --}}
                    @if (!empty($paypal['url']))
                        <x-ui.button :href="$paypal['url']" variant="primary" size="sm"
                                     target="_blank" rel="noopener noreferrer">
                            {{ __('rahmen.spenden.paypal_knopf') }}
                            <span class="sr-only">{{ __('rahmen.neuer_tab') }}</span>
                        </x-ui.button>
                    @endif
                </div>
            @endif

            @if ($projekte)
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

            @if ($bescheinigung)
                <div class="rounded-card border border-line {{ $innen }} px-5 py-5">
                    <h3 class="mb-2 font-display text-lg text-ink">{{ __('rahmen.spenden.bescheinigung') }}</h3>
                    {{-- ?? '', falls im Panel nur die E-Mail gepflegt wurde:
                         der leere Text wird beim Speichern entfernt. --}}
                    <p class="text-sm text-ink-soft">{{ $bescheinigung['text'] ?? '' }}</p>
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
    </div>
</section>
