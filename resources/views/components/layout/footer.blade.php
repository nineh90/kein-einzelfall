@php $footer = config('navigation.footer'); @endphp

<footer class="bg-green-deep px-4 pb-8 pt-10 text-on-green lg:px-10">
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

        <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
            <div>
                <h2 class="mb-3 font-display text-base">KE!N EINZELFALL e.V.</h2>
                <address class="text-sm not-italic text-on-green-soft">
                    Schiffbeker Höhe 30<br>22119 Hamburg
                </address>
            </div>

            <div>
                <h2 class="mb-3 font-display text-base">Kontakt</h2>
                <ul class="flex flex-col gap-2">
                    @foreach ($footer['kontakt'] as $link)
                        <li>
                            <a href="{{ $link['url'] }}"
                               class="text-sm text-on-green-soft no-underline hover:text-on-green hover:underline">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h2 class="mb-3 font-display text-base">Informationen</h2>
                <ul class="flex flex-col gap-2">
                    @foreach ($footer['informationen'] as $link)
                        <li>
                            <a href="{{ $link['url'] }}"
                               class="text-sm text-on-green-soft no-underline hover:text-on-green hover:underline">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h2 class="mb-3 font-display text-base">Social Media</h2>
                <ul class="flex flex-col gap-2">
                    @foreach ($footer['social'] as $link)
                        <li>
                            <a href="{{ $link['url'] }}" rel="noopener noreferrer" target="_blank"
                               class="text-sm text-on-green-soft no-underline hover:text-on-green hover:underline">
                                {{ $link['label'] }}
                                <span class="sr-only">(öffnet in neuem Tab)</span>
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
                Umsetzung:
                <a href="https://nils-digital.de" rel="noopener" target="_blank"
                   class="text-on-green underline">Nils-Digital</a>
            </span>
        </div>
    </div>
</footer>
