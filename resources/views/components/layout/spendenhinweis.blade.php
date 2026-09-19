{{--
    Spendenhinweis für wiederkehrende Besucherinnen (KEV-6).

    Steht im HTML jeder erlaubten Seite (App\Support\SpendenHinweis), aber mit
    `hidden`: Sichtbar wird er erst, wenn das Skript ihn hervorholt — und das
    tut es nur, wenn der Zähler im Browser die Schwelle erreicht hat
    (resources/js/spendenhinweis.js). Ohne JavaScript gibt es ihn nicht, und
    das ist richtig so: Ohne Skript gäbe es auch keinen Zähler, also kein
    „wiederkehrend“, und ein Kasten, der bei jedem Aufruf da wäre, ist genau
    das, was nicht gewollt ist.

    Kein <dialog>, keine Fokusfalle, kein Abdunkeln: Der Kasten ist ein
    Landmark am Seitenende (<aside>), den eine Vorlesehilfe in ihrer Liste
    findet, der aber niemanden unterbricht. Wer gerade liest, liest weiter.

    Kein <h2>: Der Kasten steht nach der Fusszeile; eine Überschrift hier wäre
    die letzte der Seite und gehörte in keine Gliederung. Der Name kommt über
    aria-labelledby, wie beim Hinweis zu belastenden Inhalten.

    Die Schwellen stehen als data-Attribute am Element und nicht im Bundle:
    So gilt config/spendenhinweis.php ohne neuen Asset-Build.
--}}
<aside id="spendenhinweis"
       hidden
       data-spendenhinweis
       data-ab="{{ (int) config('spendenhinweis.ab_aufrufen') }}"
       data-ruhe-tage="{{ (int) config('spendenhinweis.ruhe_tage') }}"
       aria-labelledby="spendenhinweis-titel"
       class="spendenhinweis fixed right-4 z-30 w-[calc(100%-2rem)] max-w-sm rounded-card border
              border-line bg-card p-5 shadow-sm">

    <x-ui.eyebrow class="mb-2">{{ __('rahmen.spendenhinweis.eyebrow') }}</x-ui.eyebrow>

    <p id="spendenhinweis-titel" class="mb-1 font-display text-lg font-medium text-ink">
        {{ __('rahmen.spendenhinweis.titel') }}
    </p>

    <p class="mb-4 text-sm leading-relaxed text-ink-soft">
        {{ __('rahmen.spendenhinweis.text') }}
    </p>

    <div class="flex flex-wrap items-center gap-3">
        {{-- Ein Link, kein Knopf: Er führt woandershin. Wer ihn nimmt, bekommt
             dieselbe Ruhe wie beim Wegklicken — auf der Spendenseite gewesen zu
             sein ist Antwort genug. --}}
        <x-ui.button :href="\App\Models\Language::aktuell()->pfad(config('spendenhinweis.ziel'))"
                     variant="primary" size="sm" data-spendenhinweis-ziel>
            {{ __('rahmen.spendenhinweis.knopf') }}
        </x-ui.button>

        <button type="button" data-spendenhinweis-schliessen
                class="rounded-full px-3 py-2 text-sm text-ink-soft underline hover:text-ink">
            {{ __('rahmen.spendenhinweis.spaeter') }}
        </button>
    </div>

    {{-- Das X oben rechts, für alle, die den Kasten einfach nur weghaben
         wollen. Dieselbe Wirkung wie „Jetzt nicht“ — zwei Wege, ein Ziel. --}}
    <button type="button" data-spendenhinweis-schliessen
            class="absolute right-2 top-2 flex h-9 w-9 items-center justify-center rounded-full
                   text-ink-soft hover:bg-green-mist hover:text-ink">
        <span class="sr-only">{{ __('rahmen.spendenhinweis.schliessen') }}</span>
        <x-ui.icon name="close" :size="18" />
    </button>
</aside>
