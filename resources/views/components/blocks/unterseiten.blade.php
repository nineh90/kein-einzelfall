@props([
    'seite' => null,     // die Seite, auf der der Baustein steht
    'titel' => null,
    'aufrufe' => false,  // Aufrufe je Kachel zeigen (KEV-156, Taddis Frage nach einem Zähler)
    'auf' => 'cream',    // cream | card
])

@php
    use App\Support\Titelbilder;
    use App\Support\Unterseiten;
    use Illuminate\Support\Number;

    $kacheln = $seite ? Unterseiten::fuer($seite) : [];

    // Kacheln stehen auf der jeweils anderen Fläche, sonst verschwämmen sie
    // mit dem Hintergrund.
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';

    $ueberschrift = 'unterseiten-'.($seite?->getKey() ?? 'x');
@endphp

{{--
    Unterseiten als Kacheln (KEV-156).

    Für die Übersicht „Raum für Ungesagtes“: Taddis vier Kategorien, jede mit
    ihrem Titelbild. Was auf den Kacheln steht, kommt aus Menü und Seiten
    (App\Support\Unterseiten), hier wird nichts gepflegt außer der Überschrift
    und dem Schalter für die Aufrufe.

    Die Aufrufe zählt der Server ohne jede Kennung (App\Support\Aufrufe). Steht
    eine Seite noch bei null, fehlt die Zeile, statt „0 Aufrufe“ zu zeigen: Das
    läse sich wie „hier schaut niemand hin“, und genau das Gefühl soll dieser
    Bereich nicht geben.
--}}
@if (count($kacheln) > 0)
<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
]) @if ($titel) aria-labelledby="{{ $ueberschrift }}" @else aria-label="{{ $seite->titel }}" @endif>
    <div class="mx-auto max-w-6xl">
        @if ($titel)
            <h2 id="{{ $ueberschrift }}" class="mb-6 font-display text-2xl font-semibold text-green">{{ $titel }}</h2>
        @endif

        {{-- Eine Spalte auf dem Handy, zwei ab „sm“, ab „lg“ alle vier in
             einer Reihe. minmax(0,1fr) und min-w-0 wie bei den
             Einstiegskarten: Kein langes Wort drückt eine Kachel breiter. --}}
        <ul class="grid grid-cols-[minmax(0,1fr)] gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($kacheln as $kachel)
                @php
                    $bild = $kachel['bild'];
                    $klein = $bild ? preg_replace('/\.webp$/', '-1000.webp', $bild) : null;
                    $klein = $klein && $klein !== $bild && \App\Support\Medien::vorhanden($klein) ? $klein : $bild;
                @endphp
                <li class="flex min-w-0">
                    {{-- Die ganze Kachel ist der Link: ein Tab-Stopp, große
                         Trefferfläche. --}}
                    <a href="{{ $kachel['url'] }}"
                       class="group flex min-w-0 flex-1 flex-col overflow-hidden rounded-card border border-line {{ $innen }}
                              hyphens-auto break-words [hyphenate-limit-chars:14_6_6] no-underline hover:border-green">
                        @if ($bild)
                            {{-- Schmuck: Der Name der Seite steht darunter.
                                 Links ausgerichtet, denn Taddis Bilder sind
                                 rechts um Wand verlängert und tragen ihr Motiv
                                 links; die Höhe wie im Seitenkopf. --}}
                            <span class="block aspect-[3/2] overflow-hidden border-b border-line">
                                <img src="{{ bildadresse($klein) }}" alt="" loading="lazy" decoding="async"
                                     width="1000" height="388"
                                     class="h-full w-full object-cover"
                                     style="object-position: {{ Titelbilder::fokus($bild) }}">
                            </span>
                        @endif

                        <span class="flex flex-1 flex-col p-4 lg:p-5">
                            <span class="font-display text-lg font-semibold text-ink group-hover:underline">
                                {{ $kachel['label'] }}
                            </span>

                            @if (filled($kachel['text']))
                                <span class="mt-1 text-[0.875rem] leading-relaxed text-ink-soft">
                                    {{ $kachel['text'] }}
                                </span>
                            @endif

                            <span class="mt-auto flex flex-wrap items-center justify-between gap-x-3 gap-y-1 pt-4 text-[0.8125rem]">
                                @if ($aufrufe && $kachel['aufrufe'])
                                    <span class="flex items-center gap-1.5 text-ink-soft" data-aufrufe>
                                        <x-ui.icon name="eye" :size="16" />
                                        {{ trans_choice(':anzahl Aufruf|:anzahl Aufrufe', $kachel['aufrufe'], [
                                            'anzahl' => Number::format($kachel['aufrufe'], locale: app()->getLocale()),
                                        ]) }}
                                    </span>
                                @endif
                                <span class="ml-auto text-green-deep">
                                    {{ __('Ansehen') }} <span aria-hidden="true">→</span>
                                </span>
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
