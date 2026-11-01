@props([
    'kachel',              // ['label', 'url', 'bild', 'text', 'aufrufe'], siehe App\Support\Unterseiten
    'innen' => 'bg-card',  // Fläche der Kachel, die Gegenfläche des Abschnitts
    'aufrufe' => false,    // Aufrufe zeigen (KEV-156)
    // Bildausschnitt. Neben einem Text (KEV-174) flacher, sonst stehen zwei
    // Kacheln untereinander höher als der Text daneben.
    'format' => 'aspect-[3/2]',
    // KEV-293: Name im Bild statt darunter. Taddis Bilder laufen rechts
    // hell aus, dort steht er. Nur mit Bild.
    'titelImBild' => false,
])

{{-- Eine Seite als Kachel mit Titelbild, Name und Unterzeile. Für die
     Unterseiten eines Bereichs (KEV-156) und für ausgewählte Seiten neben
     einem Text (KEV-174). --}}
@php
    use App\Support\Titelbilder;
    use Illuminate\Support\Number;

    $bild = $kachel['bild'];
    $klein = $bild ? preg_replace('/\.webp$/', '-1000.webp', $bild) : null;
    $klein = $klein && $klein !== $bild && \App\Support\Medien::vorhanden($klein) ? $klein : $bild;
    $imBild = $titelImBild && $bild;
@endphp

{{-- Die ganze Kachel ist der Link: ein Tab-Stopp, große
     Trefferfläche. --}}
<a href="{{ $kachel['url'] }}"
   class="group flex min-w-0 flex-1 flex-col overflow-hidden rounded-card border border-line {{ $innen }}
          hyphens-auto break-words [hyphenate-limit-chars:14_6_6] no-underline hover:border-green">
    @if ($bild)
        {{-- Schmuck: Der Name der Seite steht darunter (mit $titelImBild im Bild).
             Links ausgerichtet, denn Taddis Bilder sind
             rechts um Wand verlängert und tragen ihr Motiv
             links; die Höhe wie im Seitenkopf. --}}
        <span class="relative block {{ $format }} overflow-hidden border-b border-line">
            <img src="{{ bildadresse($klein) }}" alt="" loading="lazy" decoding="async"
                 width="1000" height="388"
                 class="h-full w-full object-cover"
                 style="object-position: {{ Titelbilder::fokus($bild) }}">
            @if ($imBild)
                <span class="absolute inset-y-0 right-0 flex w-1/2 items-center justify-end p-4 text-right lg:p-5">
                    <span class="font-display text-xl font-semibold leading-tight text-ink group-hover:underline sm:text-2xl">
                        {{ $kachel['label'] }}
                    </span>
                </span>
            @endif
        </span>
    @endif

    <span class="flex flex-1 flex-col p-4 lg:p-5">
        @unless ($imBild)
            <span class="font-display text-lg font-semibold text-ink group-hover:underline">
                {{ $kachel['label'] }}
            </span>
        @endunless

        @if (filled($kachel['text']))
            <span @class([
                'text-[0.875rem] leading-relaxed text-ink-soft',
                'mt-1' => ! $imBild,
            ])>
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
