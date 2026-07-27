@php
    $optionen = config('darstellung.optionen');
@endphp

{{--
    Barrierefreiheits-Toolbar.

    Im Mockup ist das ein <div> mit title-Attribut — nicht fokussierbar, kein
    Accessible Name. Hier ein echter Button mit aria-expanded und aria-controls.

    Vollständig serverseitig gerendert (Beschriftungen aus config/darstellung.php).
    Vorher baute Alpine das Panel per x-for aus einer Liste im JavaScript-Bundle
    zusammen. Das war aus zwei Gründen falsch:

      1. Unsere Content-Security-Policy verbietet 'unsafe-eval'. Alpine wertet
         jeden Ausdruck in @click/x-show/x-text über new Function() aus — der
         Browser blockiert das, und zwar lautlos für den Benutzer. Der Knopf war
         damit tot, obwohl am HTML nichts zu sehen war.
      2. Ein Panel, das erst JavaScript erzeugt, ist vor dem Ausführen des
         Skripts nicht vorhanden. Ausgerechnet die Barrierefreiheits-Einstellungen
         sollten nicht die fragilste Stelle der Seite sein.

    Die Bedienung sitzt jetzt in resources/js/a11y.js und arbeitet ausschliesslich
    über data-Attribute — kein zur Laufzeit ausgewerteter Quelltext, keine
    Aufweichung der CSP nötig.
--}}
<div class="relative">
    <button type="button"
            data-a11y-oeffnen
            aria-expanded="false"
            aria-controls="a11y-panel"
            class="relative flex h-10 w-10 items-center justify-center rounded-full border
                   border-line bg-card text-green hover:bg-green-mist">
        <span class="sr-only">Darstellung und Barrierefreiheit einstellen</span>
        <x-ui.icon name="accessibility" :size="18" />

        {{-- Zähler zeigt, dass Einstellungen aktiv sind — sonst wundert man sich
             auf einem fremden Gerät über das veränderte Aussehen. --}}
        <span data-a11y-zaehler hidden
              class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center
                     rounded-full bg-green px-1 text-[0.625rem] text-on-green"></span>
    </button>

    {{--
        Sichtbarkeit über das HTML-Attribut `hidden`, nicht über eine CSS-Klasse.

        Es wirkt auch dann, wenn die Stilvorlage nicht geladen hat (der Browser
        versteckt [hidden] von sich aus) — und ohne JavaScript bleibt es gesetzt,
        was richtig ist: Das Panel wäre dann ohnehin nicht bedienbar.
    --}}
    <div id="a11y-panel"
         hidden
         role="dialog"
         aria-labelledby="a11y-titel"
         class="fixed inset-x-2 top-20 z-50 max-h-[75vh] overflow-y-auto rounded-card border
                border-line bg-card p-4 shadow-lg
                sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 sm:w-80">

        <div class="mb-3 flex items-center justify-between gap-4">
            {{-- Bewusst kein <h2>: Die Toolbar steht im Quelltext vor der <h1> der
                 Seite. Als Überschrift ausgezeichnet würde sie die Dokument-Outline
                 anführen und Screenreader-Nutzer in die Irre schicken. Der Dialog
                 ist über aria-labelledby trotzdem sauber benannt. --}}
            <p id="a11y-titel" class="font-display text-lg text-ink">Darstellung</p>
            <button type="button" data-a11y-schliessen
                    class="flex h-8 w-8 items-center justify-center rounded-full text-ink-soft hover:bg-green-mist">
                <span class="sr-only">Schließen</span>
                <x-ui.icon name="close" :size="18" />
            </button>
        </div>

        @foreach ($optionen as $schluessel => $opt)
            <div class="border-b border-line py-3 last:border-0">

                @if ($opt['typ'] === 'schalter')
                    {{-- Einfacher Schalter --}}
                    <button type="button"
                            data-a11y-umschalten="{{ $schluessel }}"
                            aria-pressed="false"
                            class="group flex w-full items-center justify-between gap-3 text-left">
                        <span class="text-sm text-ink">{{ $opt['label'] }}</span>
                        <span class="relative h-6 w-11 shrink-0 rounded-full bg-line transition-colors
                                     group-aria-[pressed=true]:bg-green">
                            <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-card transition-transform
                                         group-aria-[pressed=true]:translate-x-5"></span>
                        </span>
                    </button>
                @else
                    {{-- Stufen (Schriftgröße, Abstände) und Auswahl (Kontrast) --}}
                    <p class="mb-2 text-sm font-medium text-ink" id="a11y-{{ $schluessel }}-label">
                        {{ $opt['label'] }}
                    </p>
                    <div class="flex flex-wrap gap-1.5" role="group"
                         aria-labelledby="a11y-{{ $schluessel }}-label">
                        @foreach ($opt['werte'] as $eintrag)
                            <button type="button"
                                    data-a11y-setzen="{{ $schluessel }}"
                                    data-a11y-wert="{{ $eintrag['wert'] }}"
                                    @if ($opt['typ'] === 'stufen') data-a11y-zahl @endif
                                    aria-pressed="false"
                                    class="rounded-full border border-line px-3 py-1.5 text-xs
                                           aria-[pressed=true]:border-green aria-[pressed=true]:bg-green
                                           aria-[pressed=true]:text-on-green">
                                {{ $eintrag['label'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        <button type="button" data-a11y-zuruecksetzen
                class="mt-3 w-full rounded-full border border-line py-2 text-sm text-ink-soft hover:bg-green-mist">
            Alles zurücksetzen
        </button>

        <p class="mt-3 text-xs text-ink-soft">
            Die Einstellungen bleiben auf diesem Gerät gespeichert.
            <a href="/barrierefreiheit" class="text-green-deep underline">Mehr zur Barrierefreiheit</a>
        </p>
    </div>
</div>
