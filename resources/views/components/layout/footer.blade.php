@php $footer = \App\Support\Navigation::fusszeile(); @endphp

{{-- Unten der Ausgleich für die feste Leiste auf dem Handy (4 rem plus die
     Home-Leiste des iPhones). Bis KEV-26 stand dafür ein eigener Streifen
     unter dem Fuss — als heller Balken unter dem Grün. --}}
<footer class="bg-green-deep px-4 md:px-8 pb-[calc(6rem+env(safe-area-inset-bottom))] pt-10 text-on-green lg:px-10 xl:pb-8">
    <div class="mx-auto max-w-6xl">

        {{-- Unter 12 rem neben dem Logo rutschen Name und Motto darunter: Bei
             320 px und größter Schrift brachen sie sonst mitten im Wort
             („EINZELFAL“ / „L“). In rem, damit die Grenze mitwächst. --}}
        <div class="mb-8 flex flex-wrap items-center gap-3">
            {{-- Das Logo ist schwarz auf transparent und auf dunklem Grund unsichtbar.
                 Bis eine helle Vektor-Variante vorliegt: cremefarbenes Badge als Träger. --}}
            <span class="flex h-13 w-13 shrink-0 items-center justify-center rounded-full bg-cream p-2.5">
                <img src="{{ bildadresse('/img/logo.png') }}" alt="" width="52" height="52" loading="lazy" decoding="async"
                     class="h-full w-full object-contain">
            </span>
            <div class="min-w-0 flex-1 basis-48">
                <span class="block font-display text-lg">KE!N EINZELFALL e.V.</span>
                {{-- Das Motto des Vereins in der Handschrift (Kundenwunsch, KEV-122),
                     im hellen Grün wie die Handschrift im CTA-Band. Jeder Satz
                     als Block: Auf dem Handy bricht es zwischen den Sätzen um,
                     nicht in „Für eine / Stimme“. --}}
                <p class="font-hand text-xl leading-tight text-on-green-hand">
                    @foreach (preg_split('/(?<=\.)\s+/', __('Für Sichtbarkeit. Für eine Stimme. Für Gehör.')) as $satz)
                        <span class="inline-block">{{ $satz }}</span>
                    @endforeach
                </p>
            </div>
        </div>

        {{-- Notfall-Zeile auf jeder Seite (KEV-42). Das Hinweisfenster sagt „Die
             Notfallnummern stehen am Ende jeder Seite“, und hier stehen sie.
             Seit KEV-123 dieselben Nummern wie in der Box der Startseite, in
             denselben zwei Spalten, aber ohne Polizei und Feuerwehr (Taddi).
             Auf dem dunkelgrünen Grund in der hellen Schrift der Fusszeile,
             nicht im Warnrot: Das hätte hier keinen Kontrast.
             Ab „lg“ mehr Abstand zwischen den beiden Spalten (KEV-171, Taddi):
             Mit 24 px lasen sich die Namen links und die Nummern rechts wie
             eine Zeile. --}}
        @php
            $alle = config('hilfe.nummern');
            $spalten = array_map(
                fn (array $schluessel) => array_values(array_filter(array_map(fn ($s) => $alle[$s] ?? null, $schluessel))),
                array_values(config('hilfe.kompakt')),
            );
        @endphp
        <section aria-labelledby="sofort-hilfe-titel"
                 class="mb-8 flex flex-col gap-2 border-y border-on-green-line py-4 text-sm lg:flex-row lg:items-baseline lg:gap-6">
            <h2 id="sofort-hilfe-titel" class="shrink-0 font-display text-base">{{ __('rahmen.fusszeile.sofort_hilfe') }}:</h2>
            <div class="grid gap-x-6 gap-y-1 sm:grid-cols-2 lg:gap-x-16 xl:gap-x-24">
                @foreach ($spalten as $nummern)
                    <ul class="flex flex-col gap-1">
                        @foreach ($nummern as $n)
                            <li>
                                {{-- Die Nummer bricht nie um: „0800 123 99“ / „00“
                                     sähe aus wie zwei Nummern. Der Name als Block
                                     daneben oder ganz darunter, sonst stand
                                     „Männern“ allein unter der Nummer. --}}
                                <a href="tel:{{ $n['tel'] }}"
                                   class="group text-on-green no-underline hover:underline">
                                    <span class="whitespace-nowrap font-display text-lg font-medium">{{ $n['nummer'] }}</span>
                                    {{-- Die Unterstreichung vererbt sich nicht in einen inline-block. --}}
                                    <span class="inline-block text-on-green-soft group-hover:underline">{{ __($n['name']) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        </section>

        {{-- Vier Spalten erst ab „lg“: Bei 768 px blieben je 170 px, und die
             E-Mail-Adresse brach mitten im Wort. Auf dem Handy stehen Adresse
             und Kontakt über die volle Breite, die beiden Linklisten
             nebeneinander. Die Kontaktspalte etwas breiter (KEV-124): Bei
             1024 px blieben sonst 212 px, arbeitsgruppe@… braucht 223. --}}
        <div class="grid grid-cols-2 gap-x-6 gap-y-8 lg:grid-cols-[1fr_1.25fr_1fr_1fr] lg:gap-8">
            <div class="col-span-2 sm:col-span-1">
                <h2 class="mb-3 font-display text-base">KE!N EINZELFALL e.V.</h2>
                <address class="text-sm not-italic text-on-green-soft">
                    Schiffbeker Höhe 30<br>22119 Hamburg
                </address>
            </div>

            <div class="col-span-2 sm:col-span-1">
                <h2 class="mb-3 font-display text-base">{{ __('rahmen.fusszeile.kontakt') }}</h2>
                <ul class="flex flex-col">
                    {{-- Muss eine Adresse umbrechen (grössere Schrift aus der
                         Darstellung), dann nach dem @ und nie am Bindestrich:
                         „arbeitsgruppe@kein-“ / „einzelfall.de“ liest sich
                         wie zwei Adressen. --}}
                    @foreach ($footer['kontakt'] as $link)
                        <li>
                            <a href="{{ $link['url'] }}"
                               class="inline-block py-1.5 text-sm text-on-green-soft no-underline hover:text-on-green hover:underline">
                                @if (str_starts_with($link['url'], 'mailto:'))
                                    {{ Str::before($link['label'], '@') }}@<wbr><span class="whitespace-nowrap">{{ Str::after($link['label'], '@') }}</span>
                                @else
                                    {{ $link['label'] }}
                                @endif
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
