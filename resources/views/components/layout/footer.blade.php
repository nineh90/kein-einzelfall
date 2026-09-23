@php $footer = \App\Support\Navigation::fusszeile(); @endphp

{{-- Unten der Ausgleich für die feste Leiste auf dem Handy (4 rem plus die
     Home-Leiste des iPhones). Bis KEV-26 stand dafür ein eigener Streifen
     unter dem Fuss — als heller Balken unter dem Grün. --}}
<footer class="bg-green-deep px-4 md:px-8 pb-[calc(6rem+env(safe-area-inset-bottom))] pt-10 text-on-green lg:px-10 lg:pb-8">
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
