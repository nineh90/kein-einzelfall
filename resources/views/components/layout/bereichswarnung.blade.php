@props(['seite' => null])

@if ($seite)
@php
    $sprache = \App\Models\Language::aktuell();

    // Fehlt die Warnung in dieser Sprache, kommt die deutsche
    // (App\Support\Bereichswarnung). Dann auszeichnen, damit Vorlesehilfen
    // richtig sprechen.
    $ersatz = $seite->locale !== $sprache->code ? \App\Models\Language::finden($seite->locale) : null;
@endphp
{{--
    Eigene Triggerwarnung vor einem Bereich (KEV-156, „Raum für Ungesagtes“).

    Gebaut wie der allgemeine Hinweis (trigger-warnung.blade.php), aus
    denselben Gründen: Ohne JavaScript ist `<dialog open>` ein Block im
    Seitenfluss vor dem Inhalt, mit JavaScript stuft bereichswarnung.js ihn zum
    echten Dialog hoch. Steht der allgemeine Hinweis auch noch offen, kommt
    dieser erst nach ihm, nie beide übereinander.

    Anders als dort gibt es kein „nicht mehr anzeigen“: Taddi will die Warnung
    vor diesem Bereich jedes Mal, „bevor man weiter kommt“. Bestätigt gilt sie
    für den Rest des Besuchs, damit sie nicht vor jeder Unterseite neu kommt.

    Drei Wege: weiter, zurück zur Startseite (wer diesen Bereich jetzt nicht
    sehen will, muss nicht gleich die Website verlassen) und der Notausgang.
    Der gehört hinein, weil der Knopf oben hinter dem offenen Dialog nicht
    erreichbar ist. Zurück und Notausgang sind Links und gehen auch ohne
    JavaScript.
--}}
<dialog id="bereichswarnung"
        open
        data-bereich="{{ $seite->slug }}"
        aria-labelledby="bereichswarnung-ueberschrift bereichswarnung-titel"
        @if ($ersatz) lang="{{ $ersatz->code }}" dir="{{ $ersatz->richtung }}" @endif>

    {{-- Etwas breiter als der allgemeine Hinweis (max-w-2xl): Erst so passen
         die drei Knöpfe ab „lg“ nebeneinander, ohne umzubrechen. Die Zeilen
         des Textes begrenzt max-w-prose. --}}
    <div class="mx-auto w-full max-w-3xl rounded-card border border-line bg-card p-6 text-center lg:p-8">

        <p class="mb-2 font-display text-xs font-semibold uppercase tracking-[0.14em] text-ink-soft">
            {{ __('rahmen.bereichswarnung.eyebrow') }}
        </p>

        {{-- <p> statt Überschrift, wie beim allgemeinen Hinweis: Er steht im
             Quelltext vor der h1 der Seite. Den Namen liefert aria-labelledby. --}}
        <p id="bereichswarnung-ueberschrift"
           class="mb-2 font-display text-3xl font-medium leading-tight text-green lg:text-4xl">
            {{ __('rahmen.trigger.ueberschrift') }}
        </p>

        <p id="bereichswarnung-titel"
           class="mb-4 font-display text-xl font-medium text-ink lg:text-2xl">
            {{ $seite->titel }}
        </p>

        {{-- Alle Absätze gleich dunkel, wie im allgemeinen Hinweis (KEV-42). --}}
        <div class="mx-auto flex max-w-prose flex-col gap-4 text-ink">
            @foreach ($seite->triggerwarnung as $absatz)
                <p>{{ hervorheben($absatz) }}</p>
            @endforeach
        </div>

        <div class="mt-8 border-t border-line pt-6">
            {{-- Untereinander bis „lg“: Drei Knöpfe passen erst dann
                 nebeneinander, und wer in einer angespannten Lage tippt,
                 trifft eine ganze Zeile zuverlässiger als eine halbe. Darunter
                 gleich breit und mittig statt über die ganze Karte. --}}
            <div class="mx-auto flex max-w-sm flex-col gap-3 lg:max-w-none lg:flex-row lg:justify-center lg:[&>*]:whitespace-nowrap">
                <x-ui.button type="button"
                             class="w-full lg:w-auto"
                             data-bereichswarnung-weiter
                             data-bereichswarnung-braucht-js>
                    {{ __('rahmen.bereichswarnung.weiter') }}
                </x-ui.button>

                <x-ui.button :href="$sprache->pfad('/')"
                             variant="ghost"
                             class="w-full lg:w-auto">
                    {{ __('rahmen.bereichswarnung.zurueck') }}
                </x-ui.button>

                <x-ui.button :href="config('navigation.exit_url')"
                             variant="alert"
                             class="w-full lg:w-auto"
                             rel="noreferrer noopener"
                             data-notausgang>
                    <x-ui.icon name="exit" :size="18" />
                    {{ __('rahmen.trigger.verlassen') }}
                </x-ui.button>
            </div>

            <p class="mt-5 text-sm text-ink-soft" data-bereichswarnung-ohne-js>
                {{ __('rahmen.bereichswarnung.ohne_js') }}
            </p>
        </div>
    </div>
</dialog>
@endif
