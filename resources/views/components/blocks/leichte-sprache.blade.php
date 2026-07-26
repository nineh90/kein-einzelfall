@props(['offen' => false])

{{--
    Zusammenfassung in Leichter Sprache (WCAG AAA, 3.1.5).

    Steht bewusst GANZ OBEN auf einer Seite, nicht am Ende: wer sie braucht,
    soll nicht erst den schweren Text durchqueren müssen.

    Typografische Regeln der Leichten Sprache, die hier im CSS abgebildet sind:
    grössere Schrift, weiter Zeilenabstand, Flattersatz (kein Blocksatz),
    kurze Zeilen, keine Kursivschrift.

    Wichtig: Die Texte schreibt der Verein, nicht wir. Leichte Sprache ist eine
    eigene Disziplin mit Regelwerk und gehört idealerweise von einer Prüfgruppe
    aus der Zielgruppe abgenommen.
--}}
<details {{ $offen ? 'open' : '' }}
         class="group rounded-card border-2 border-green-mist bg-card">

    <summary class="flex cursor-pointer items-center gap-3 px-5 py-4 marker:content-none
                    [&::-webkit-details-marker]:hidden">
        <span class="flex-1 font-medium text-ink">
            Diese Seite in Leichter Sprache
        </span>
        <span class="shrink-0 text-ink-soft transition-transform group-open:rotate-180">
            <x-ui.icon name="chevron-down" :size="20" />
        </span>
    </summary>

    <div class="border-t border-green-mist px-5 py-5
                text-[1.125rem] leading-[2] [text-align:left] [hyphens:none]
                [&_p]:mb-4 [&_p]:max-w-[55ch] [&_em]:not-italic [&_i]:not-italic
                [&_li]:mb-2 [&_ul]:list-disc [&_ul]:pl-5">
        {{ $slot }}
    </div>
</details>
