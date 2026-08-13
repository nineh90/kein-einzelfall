@props([
    'titel' => 'Jetzt spenden',
    'bank' => null,        // ['institut'=>, 'iban'=>, 'bic'=>]
    'paypal' => null,      // ['empfaenger'=>, 'url'=>]
    'projekte' => [],      // [['titel'=>, 'widget'=>, 'url'=>], ...]
    'bescheinigung' => null,
])

<section class="px-4 py-8 lg:px-10 lg:py-12" aria-labelledby="spenden-titel">
    <div class="mx-auto max-w-3xl">

        <h2 id="spenden-titel" class="mb-6 font-display text-2xl font-medium text-ink">
            {{ $titel }}
        </h2>

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

                <div class="rounded-card border border-line bg-card px-5 py-5">
                    <h3 class="mb-3 font-display text-lg text-ink">
                        Überweisung
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
                            <figure class="shrink-0 sm:w-40">
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
                                     aria-label="QR-Code mit der Bankverbindung des Vereins zum Einlesen in einer Banking-App">
                                    {!! $girocode !!}
                                </div>
                                <figcaption class="mt-2 text-xs text-ink-soft">
                                    Mit der Banking-App scannen — die Überweisung ist dann
                                    schon ausgefüllt. Den Betrag gibst du selbst ein.
                                </figcaption>
                            </figure>
                        @endif
                    </div>
                </div>
            @endif

            @if ($paypal)
                <div class="rounded-card border border-line bg-card px-5 py-5">
                    <h3 class="mb-2 font-display text-lg text-ink">PayPal</h3>
                    @if (!empty($paypal['empfaenger']))
                        <p class="mb-4 text-sm text-ink-soft">
                            Empfänger: {{ $paypal['empfaenger'] }}
                        </p>
                    @endif
                    {{-- Reiner Link, kein eingebettetes Skript: Solange niemand
                         klickt, erfährt PayPal nichts von diesem Besuch. --}}
                    <x-ui.button :href="$paypal['url']" variant="primary" size="sm"
                                 target="_blank" rel="noopener noreferrer">
                        Bei PayPal spenden
                        <span class="sr-only">(öffnet in neuem Tab)</span>
                    </x-ui.button>
                </div>
            @endif

            @if ($projekte)
                <div class="rounded-card border border-line bg-card px-5 py-5">
                    <h3 class="mb-1 font-display text-lg text-ink">Projekte auf betterplace.org</h3>
                    <p class="mb-4 text-sm text-ink-soft">
                        Für ein bestimmtes Vorhaben spenden.
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
                <div class="rounded-card border border-line bg-card px-5 py-5">
                    <h3 class="mb-2 font-display text-lg text-ink">Spendenbescheinigung</h3>
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
</section>
