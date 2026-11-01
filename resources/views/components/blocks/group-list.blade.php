@props([
    'titel' => null,
    'einleitung' => null,
    'typ' => 'selbsthilfe',   // selbsthilfe | arbeits
    'auf' => 'cream',
    // KEV-309 (Taddi auf /selbsthilfegruppen): nicht nach „offen“ und „in
    // Planung“ gliedern. Lieber wirkt eine Gruppe, als sei die Anmeldung
    // gerade zu, als dass sie noch nicht gestartet ist.
    'ohne_status' => false,
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
    $geteilt = ! $ohne_status && $offene->isNotEmpty() && $spaetere->isNotEmpty();

    $spaeterTitel = $spaetere->every(fn ($g) => $g->status === 'geplant')
        ? __(\App\Models\Group::STATUS['geplant'])
        : __('Geplant oder zurzeit pausiert');
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
                    class="mb-4 font-display text-2xl font-medium text-green lg:text-3xl">
                    {{ $titel }}
                </h2>
            @endif

            @if ($einleitung)
                <p class="mb-8 max-w-prose leading-relaxed text-ink-soft">{{ $einleitung }}</p>
            @endif

            {{-- Ohne Gliederung (KEV-309) eine durchgehende Liste, offene
                 zuerst. Sonst bliebe vor den späteren Gruppen eine Lücke in
                 der letzten Reihe. --}}
            @php
                $listen = $ohne_status
                    ? [[$offene->concat($spaetere), null]]
                    : [[$offene, __(\App\Models\Group::STATUS['offen'])], [$spaetere, $spaeterTitel]];
            @endphp
            @foreach ($listen as [$liste, $zwischentitel])
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

                {{-- Drei Spalten. Die AGs hatten bis KEV-84 zwei, weil ihr Text
                     in der Karte aufklappte; der steht jetzt auf eigenen Seiten. --}}
                <ul class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($liste as $gruppe)
                        <li>
                            <x-blocks.gruppen-karte :gruppe="$gruppe" :auf="$auf" :ebene="$geteilt ? 'h4' : 'h3'" :status_sichtbar="$geteilt"
                                :status_nennen="! $ohne_status" />
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </div>
    </section>
@endif
