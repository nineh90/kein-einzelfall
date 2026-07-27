@props(['fassungen' => [], 'variant' => 'kopf'])

@php
    use App\Models\Language;

    $aktiv = Language::aktive();
    $jetzt = Language::aktuell();
@endphp

{{--
    Sprachumschalter.

    Bewusst eine schlichte Linkliste und kein Aufklapper:

    - Kein JavaScript. Die CSP erlaubt kein 'unsafe-eval', und ein Umschalter,
      der von einem Bundle abhängt, ist genau dann kaputt, wenn die Verbindung
      schlecht ist — also unterwegs, also in der Situation, für die diese Seite
      gebaut ist.
    - Kein <select> mit onchange: das wäre wieder Skript, und ohne Skript
      unbedienbar.
    - Zwei bis vier Sprachen passen als Linkliste nebeneinander. Wird die Liste
      länger, ist ein <details>-Aufklapper der nächste Schritt — dasselbe Muster
      wie beim Mobilmenü, ebenfalls ohne eine Zeile JavaScript.

    Der Notausgang bleibt unberührt — und zwar gemessen, nicht gehofft:
    In der Kopfzeile bei 360 px hat der Umschalter Notausgang und Menüknopf
    aus dem Bild geschoben (451 px Inhalt auf 360 px Breite). Deshalb steht er
    dort erst ab „sm“ und darunter im aufgeklappten Menü — dasselbe Muster,
    das der Notausgang selbst schon nutzt. Die Reihe aus Einstellungen,
    Notausgang und Menü behält damit ihre Breite und ihre Reihenfolge.
--}}
@if ($aktiv->count() > 1)
    <nav aria-label="{{ __('rahmen.sprache.auswahl') }}"
         @class([
             'flex shrink-0 items-center',
             'hidden sm:flex' => $variant === 'kopf',
         ])>
        <ul @class([
            'flex items-center',
            'gap-0.5' => $variant === 'kopf',
            'gap-1' => $variant === 'menue',
        ])>
            @foreach ($aktiv as $sprache)
                @php
                    $istAktuell = $sprache->code === $jetzt->code;
                    $ziel = $fassungen[$sprache->code] ?? $sprache->pfad('/');
                @endphp
                <li>
                    <a href="{{ $ziel }}"
                       hreflang="{{ $sprache->code }}"
                       lang="{{ $sprache->code }}"
                       @if ($istAktuell) aria-current="true" @endif
                       @class([
                           'block rounded-full no-underline text-ink-soft hover:bg-green-mist hover:text-ink'
                               .' aria-[current=true]:font-medium aria-[current=true]:text-green',
                           'px-2 py-1.5 text-xs uppercase' => $variant === 'kopf',
                           // Im Menü ist Platz: volle Trefferfläche (44 px) und die
                           // Eigenbezeichnung ausgeschrieben.
                           'flex min-h-11 items-center px-3 text-[0.9375rem]' => $variant === 'menue',
                       ])>
                        @if ($variant === 'menue')
                            {{-- Im Menü die Eigenbezeichnung: Dort sucht jemand
                                 gezielt seine Sprache und erkennt nur sie. --}}
                            {{ $sprache->label }}
                        @else
                            {{-- In der Kopfzeile nur das Kürzel: Der Platz neben
                                 Notausgang und Einstellungsknopf ist knapp. --}}
                            <span aria-hidden="true">{{ $sprache->code }}</span>
                        @endif

                        {{-- Immer eine ausgeschriebene Ansage: „RU“ vorgelesen
                             ergibt nichts, und im Menü fehlt sonst der Hinweis,
                             welche Sprache gerade gilt. --}}
                        <span class="sr-only">
                            {{ $istAktuell
                                ? __('rahmen.sprache.aktuell', ['sprache' => $sprache->bezeichnung()])
                                : __('rahmen.sprache.wechseln_zu', ['sprache' => $sprache->bezeichnung()]) }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
