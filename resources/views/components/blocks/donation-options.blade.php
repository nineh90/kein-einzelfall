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
                <div class="rounded-card border border-line bg-card px-5 py-5">
                    <h3 class="mb-3 font-display text-lg text-ink">
                        Überweisung
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
                    <p class="text-sm text-ink-soft">{{ $bescheinigung['text'] }}</p>
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
