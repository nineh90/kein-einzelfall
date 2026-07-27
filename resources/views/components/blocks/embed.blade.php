@props([
    'titel',
    'anbieter',
    'beschreibung' => null,
    'src',
    'datenschutz_url' => null,
    'hoehe' => 320,
    'direktlink' => null,
])

{{--
    Zwei-Klick-Einbettung.

    Fremde Inhalte werden erst geladen, wenn jemand das ausdrücklich möchte.
    Vorher geht kein einziger Aufruf an den Anbieter — er erfährt nicht, dass
    diese Seite besucht wurde.

    Genau das macht die Altseite falsch: Auf /spenden/ laden zwei
    betterplace-Rahmen ungefragt, obwohl ein Cookie-Banner vorhanden ist
    (das mit "blocking":false nichts blockiert).

    Umgesetzt mit <details> statt Alpine: funktioniert ohne JavaScript,
    Screenreader kennen das Muster, Tastaturbedienung inklusive.
    Der Rahmen steckt in einem <template> und existiert vor dem Aufklappen
    nicht im Dokument — sonst würde der Browser ihn sofort laden.
--}}
<div class="rounded-card border border-line bg-card">
    <details class="group" {{ $attributes }}>
        <summary class="cursor-pointer list-none px-5 py-4 marker:content-none
                        [&::-webkit-details-marker]:hidden">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 shrink-0 text-ink-soft">
                    <x-ui.icon name="shield" :size="20" />
                </span>

                <div class="flex-1">
                    <p class="font-medium text-ink">{{ $titel }}</p>

                    <p class="mt-1 text-sm text-ink-soft group-open:hidden">
                        Dieser Inhalt kommt von <strong class="font-medium">{{ $anbieter }}</strong>.
                        Wenn du ihn anzeigst, werden Daten an {{ $anbieter }} übertragen —
                        unter anderem deine IP-Adresse. Vorher passiert nichts.
                    </p>
                    <p class="mt-1 hidden text-sm text-ink-soft group-open:block">
                        Inhalt von {{ $anbieter }} wird angezeigt. Zum Ausblenden erneut auswählen.
                    </p>

                    @if ($beschreibung)
                        <p class="mt-1 text-sm text-ink-soft">{{ $beschreibung }}</p>
                    @endif

                    <span class="mt-3 inline-flex items-center gap-2 rounded-full bg-green px-4 py-2
                                 text-sm text-on-green group-open:hidden">
                        Inhalt einmalig anzeigen
                    </span>
                </div>
            </div>
        </summary>

        <div class="border-t border-line p-4">
            {{-- Der Browser lädt nichts, was in einem <template> steht.
                 Erst das kleine Skript unten hängt es beim Aufklappen ein. --}}
            <template data-embed>
                <iframe src="{{ $src }}"
                        title="{{ $titel }} ({{ $anbieter }})"
                        height="{{ $hoehe }}"
                        loading="lazy"
                        referrerpolicy="no-referrer"
                        sandbox="allow-scripts allow-same-origin allow-popups allow-forms"
                        class="w-full rounded-lg border-0"></iframe>
            </template>

            <noscript>
                <p class="text-sm text-ink-soft">
                    Zum Anzeigen dieses Inhalts wird JavaScript benötigt.
                    @if ($direktlink)
                        Du kannst ihn auch direkt bei {{ $anbieter }} öffnen.
                    @endif
                </p>
            </noscript>
        </div>
    </details>

    @if ($direktlink)
        <p class="border-t border-line px-5 py-3 text-sm">
            <a href="{{ $direktlink }}" target="_blank" rel="noopener noreferrer"
               class="text-green-deep underline">
                Stattdessen direkt bei {{ $anbieter }} öffnen
                <span class="sr-only">(öffnet in neuem Tab)</span>
            </a>
        </p>
    @endif
</div>

@once
    @push('scripts')
        <script @isset($cspNonce) nonce="{{ $cspNonce }}" @endisset>
        // Hängt den Rahmen erst beim Aufklappen ein und entfernt ihn beim
        // Zuklappen wieder — dann läuft im Hintergrund auch nichts weiter.
        document.querySelectorAll('details:has(template[data-embed])').forEach(function (d) {
            var vorlage = d.querySelector('template[data-embed]');
            var ziel = vorlage.parentElement;

            d.addEventListener('toggle', function () {
                var vorhanden = ziel.querySelector('iframe');

                if (d.open && !vorhanden) {
                    ziel.appendChild(vorlage.content.cloneNode(true));
                } else if (!d.open && vorhanden) {
                    vorhanden.remove();
                }
            });
        });
        </script>
    @endpush
@endonce
