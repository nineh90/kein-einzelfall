@props([
    'titel' => null,
    'einleitung' => null,
    'typ' => 'selbsthilfe',   // selbsthilfe | arbeits
    'auf' => 'cream',
])

@php
    $gruppen = \App\Models\Group::veroeffentlicht()
        ->vomTyp($typ)
        ->orderBy('position')
        ->orderBy('name')
        ->get();

    // Wo man mitmachen kann, steht oben; was noch nicht losgeht, darunter.
    [$offene, $spaetere] = $gruppen->partition->istOffen();

    // Zwischenüberschriften nur, wenn es beides gibt. Sonst stünde über
    // allen Karten ein Satz, der nichts unterscheidet.
    $geteilt = $offene->isNotEmpty() && $spaetere->isNotEmpty();

    $spaeterTitel = $spaetere->every(fn ($g) => $g->status === 'geplant')
        ? \App\Models\Group::STATUS['geplant']
        : 'Geplant oder zurzeit pausiert';
@endphp

@if ($gruppen->isNotEmpty())
    {{--
        Übersicht der Gruppen.

        Bewusst ohne Anmeldefunktion: Wer sich zu einer Selbsthilfegruppe
        anmeldet, offenbart damit Angaben nach Art. 9 DSGVO. Das braucht ein
        eigenes Konzept mit Löschfristen und Zugriffsregelung — hier steht
        nur, wie man Kontakt aufnimmt.
    --}}
    <section @class([
        'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
        'bg-card border-y border-line' => $auf === 'card',
    ]) aria-labelledby="gruppen-{{ $typ }}-titel">
        <div class="mx-auto max-w-6xl">
            @if ($titel)
                <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
                <h2 id="gruppen-{{ $typ }}-titel"
                    class="mb-4 font-display text-2xl font-medium text-ink lg:text-3xl">
                    {{ $titel }}
                </h2>
            @endif

            @if ($einleitung)
                <p class="mb-8 max-w-prose leading-relaxed text-ink-soft">{{ $einleitung }}</p>
            @endif

            @foreach ([[$offene, \App\Models\Group::STATUS['offen']], [$spaetere, $spaeterTitel]] as [$liste, $zwischentitel])
                @continue($liste->isEmpty())

                @if ($geteilt)
                    <h3 @class([
                        'mb-4 flex items-center gap-2.5 font-display text-lg font-medium text-ink',
                        'mt-12' => ! $loop->first,
                    ])>
                        @if ($loop->first)
                            {{-- Grüner Punkt: hier kann man mitmachen --}}
                            <span aria-hidden="true" class="size-2 rounded-full bg-green-brand"></span>
                        @endif
                        {{ $zwischentitel }}
                    </h3>
                @endif

                <ul class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($liste as $gruppe)
                        <li>
                            <x-blocks.gruppen-karte :gruppe="$gruppe" :auf="$auf" :ebene="$geteilt ? 'h4' : 'h3'" :status_sichtbar="$geteilt" />
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </div>
    </section>
@endif
