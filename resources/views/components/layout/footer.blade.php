@php $footer = \App\Support\Navigation::fusszeile(); @endphp

{{-- Unten der Ausgleich für die feste Leiste auf dem Handy (4 rem plus die
     Home-Leiste des iPhones). Bis KEV-26 stand dafür ein eigener Streifen
     unter dem Fuss — als heller Balken unter dem Grün. --}}
<footer class="bg-green-deep px-4 md:px-8 pb-[calc(6rem+env(safe-area-inset-bottom))] pt-10 text-on-green lg:px-10 xl:pb-8">
    <div class="mx-auto max-w-6xl">

        <div class="mb-8 flex items-center gap-3">
            {{-- Das Logo ist schwarz auf transparent und auf dunklem Grund unsichtbar.
                 Bis eine helle Vektor-Variante vorliegt: cremefarbenes Badge als Träger. --}}
            <span class="flex h-13 w-13 items-center justify-center rounded-full bg-cream p-2.5">
                <img src="/img/logo.png" alt="" width="52" height="52" loading="lazy" decoding="async"
                     class="h-full w-full object-contain">
            </span>
            <span class="font-display text-lg">KE!N EINZELFALL e.V.</span>
        </div>

        {{-- Notfall-Zeile auf jeder Seite (KEV-42). Das Hinweisfenster sagt „Die
             Notfallnummern stehen am Ende jeder Seite“, und hier stehen sie.
             Nummern aus config/hilfe.php, damit sie an jeder Stelle gleich sind.
             Auf dem dunkelgrünen Grund in der hellen Schrift der Fusszeile,
             nicht im Warnrot: Das hätte hier keinen Kontrast. --}}
        @php
            $alle = config('hilfe.nummern');
            $sofort = array_values(array_filter(array_map(fn ($s) => $alle[$s] ?? null, config('hilfe.fusszeile', []))));
            $sofort[] = config('hilfe.notruf')[0];
        @endphp
        <section aria-labelledby="sofort-hilfe-titel"
                 class="mb-8 flex flex-wrap items-baseline gap-x-6 gap-y-2 border-y border-on-green-line py-4 text-sm">
            <h2 id="sofort-hilfe-titel" class="font-display text-base">{{ __('rahmen.fusszeile.sofort_hilfe') }}:</h2>
            @foreach ($sofort as $n)
                <a href="tel:{{ $n['tel'] }}"
                   class="text-on-green no-underline hover:underline">
                    <span class="font-display text-lg font-medium">{{ $n['nummer'] }}</span>
                    <span class="text-on-green-soft">{{ __($n['name']) }}</span>
                </a>
            @endforeach
        </section>

        {{-- Vier Spalten erst ab „lg“: Bei 768 px blieben je 170 px, und die
             E-Mail-Adresse brach mitten im Wort. Auf dem Handy stehen Adresse
             und Kontakt über die volle Breite, die beiden Linklisten
             nebeneinander. --}}
        <div class="grid grid-cols-2 gap-x-6 gap-y-8 lg:grid-cols-4 lg:gap-8">
            <div class="col-span-2 sm:col-span-1">
                <h2 class="mb-3 font-display text-base">KE!N EINZELFALL e.V.</h2>
                <address class="text-sm not-italic text-on-green-soft">
                    Schiffbeker Höhe 30<br>22119 Hamburg
                </address>
            </div>

            <div class="col-span-2 sm:col-span-1">
                <h2 class="mb-3 font-display text-base">{{ __('rahmen.fusszeile.kontakt') }}</h2>
                <ul class="flex flex-col">
                    @foreach ($footer['kontakt'] as $link)
                        <li>
                            <a href="{{ $link['url'] }}"
                               class="inline-block py-1.5 text-sm text-on-green-soft no-underline hover:text-on-green hover:underline">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h2 class="mb-3 font-display text-base">{{ __('rahmen.fusszeile.informationen') }}</h2>
                <ul class="flex flex-col">
                    @foreach ($footer['informationen'] as $link)
                        <li>
                            <a href="{{ $link['url'] }}"
                               class="inline-block py-1.5 text-sm text-on-green-soft no-underline hover:text-on-green hover:underline">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h2 class="mb-3 font-display text-base">{{ __('rahmen.fusszeile.social') }}</h2>
                <ul class="flex flex-col">
                    @foreach ($footer['social'] as $link)
                        <li>
                            <a href="{{ $link['url'] }}" rel="noopener noreferrer" target="_blank"
                               class="inline-block py-1.5 text-sm text-on-green-soft no-underline hover:text-on-green hover:underline">
                                {{ $link['label'] }}
                                <span class="sr-only">{{ __('rahmen.neuer_tab') }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-8 flex flex-col gap-2 border-t border-on-green-line pt-5 text-xs
                    text-on-green-soft sm:flex-row sm:items-center sm:justify-between">
            <span>&copy; {{ date('Y') }} KE!N EINZELFALL e.V.</span>
            {{-- Vertraglich zugesagt: Nils-Digital muss im Footer erwähnt werden. --}}
            <span>
                {{ __('rahmen.fusszeile.umsetzung') }}
                <a href="https://nils-digital.de" rel="noopener" target="_blank"
                   class="inline-block py-1 text-on-green underline">Nils-Digital</a>
            </span>
        </div>
    </div>
</footer>
