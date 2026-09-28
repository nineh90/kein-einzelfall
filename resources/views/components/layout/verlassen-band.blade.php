{{--
    Band unten im Aufmacher der Startseite (KEV-30, Wunsch des Vereins).

    Vorher stand der Hinweis ganz unten auf der Seite, im Kontaktabschluss,
    und war dort wirkungslos: Wer ihn braucht, braucht ihn am Anfang.

    Zwei Fassungen des Satzes, je nach Gerät. Die Esc-Taste gibt es auf den
    wenigsten Handys, dafür dort die Leiste unten mit „Exit“. Die Grenze ist
    „xl“, ab da gibt es die Leiste nicht mehr. Die jeweils andere Fassung ist
    per display:none weg, Vorlesehilfen lesen also nur die passende.

    Die Vertrauenssignale müssen stimmen, siehe contact-close.blade.php.
--}}
<aside aria-label="{{ __('rahmen.verlassen.titel') }}"
       class="mx-auto mt-8 w-full max-w-6xl rounded-card border border-line bg-card/85 px-4 py-3.5 backdrop-blur-sm sm:px-5 lg:mt-10">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-6">
        <p class="flex items-start gap-2.5 text-sm text-ink">
            <span class="mt-0.5 shrink-0 text-green"><x-ui.icon name="exit" :size="18" /></span>
            <span class="xl:hidden">{{ __('rahmen.verlassen.klein') }}</span>
            <span class="hidden xl:inline">{{ __('rahmen.verlassen.gross') }}</span>
        </p>

        {{-- Auf dem Handy untereinander und mittig: nebeneinander brachen die
             drei dort ungleichmäßig um, zwei in einer Zeile, eine allein. --}}
        <ul class="flex flex-col items-center gap-y-1.5 border-t border-line pt-3 text-xs text-ink-soft
                   sm:flex-row sm:flex-wrap sm:items-start sm:gap-x-5 sm:border-0 sm:pt-0 lg:shrink-0">
            @foreach ([['lock', 'tls'], ['shield', 'vertraulich'], ['exit', 'notausgang']] as [$icon, $schluessel])
                <li class="flex items-center gap-1.5">
                    <x-ui.icon :name="$icon" :size="15" />
                    {{ __('rahmen.verlassen.'.$schluessel) }}
                </li>
            @endforeach
        </ul>
    </div>
</aside>
