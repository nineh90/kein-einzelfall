@props([
    'thema' => 'belastende Inhalte',
    'offen' => false,
])

{{--
    Inhaltshinweis (Content Note).

    Bewusst als natives <details> statt als Alpine-Komponente:
    - funktioniert ohne JavaScript
    - Screenreader kennen das Muster und melden auf/zu von sich aus
    - Tastaturbedienung kommt gratis
    - der Inhalt bleibt im DOM und damit für Suchmaschinen sichtbar

    Die Entscheidung liegt bewusst bei der lesenden Person. Wir warnen und
    bevormunden nicht: kein Zwangs-Overlay, keine Bestätigungsschaltfläche.
--}}
<details {{ $offen ? 'open' : '' }}
         class="group rounded-card border border-line bg-card">

    <summary class="flex cursor-pointer items-start gap-3 px-5 py-4 marker:content-none
                    [&::-webkit-details-marker]:hidden">
        <span class="mt-0.5 shrink-0 text-ink-soft">
            <x-ui.icon name="shield" :size="20" />
        </span>

        <span class="flex-1">
            <span class="block font-medium text-ink">
                Hinweis zum Inhalt: {{ $thema }}
            </span>
            <span class="mt-0.5 block text-sm text-ink-soft">
                <span class="group-open:hidden">
                    Der folgende Abschnitt kann belastend sein. Zum Lesen aufklappen —
                    du entscheidest, ob und wann.
                </span>
                <span class="hidden group-open:inline">
                    Zum Zuklappen erneut auswählen.
                </span>
            </span>
        </span>

        <span class="shrink-0 text-ink-soft transition-transform group-open:rotate-180">
            <x-ui.icon name="chevron-down" :size="20" />
        </span>
    </summary>

    <div class="border-t border-line px-5 py-5">
        {{ $slot }}
    </div>
</details>
