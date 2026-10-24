@props([
    'titel' => null,
    'auf' => 'cream',      // cream | card
    // Ohne eigenes Band, als Spalte neben einem Text (KEV-224, wie die
    // Dokumente neben der SHG-Einleitung)
    'rahmen' => true,
    'neben_text' => false, // Schalter aus dem Panel, hier nur aufgefangen
])

{{--
    Termin-Kalender (KEV-224): ein Monat als Raster, darunter die Termine
    dieses Monats als Liste. Veranstaltungen und Gruppentreffen, berechnet aus
    denselben Daten wie /veranstaltungen (App\Support\Termine), gepflegt wird
    nichts zusätzlich.

    Ohne JavaScript: Vor und zurück sind Links (?monat=2026-11), höchstens elf
    Monate voraus. Das Raster ist die Übersicht fürs Auge und für
    Vorlesehilfen ausgeblendet; die Liste darunter trägt dieselben Termine als
    Text mit Links.
--}}
@php
    use Carbon\CarbonImmutable;

    $heute = CarbonImmutable::now()->startOfDay();
    $erster = $heute->startOfMonth();
    $gewaehlt = preg_match('/^\d{4}-\d{2}$/', (string) request()->query('monat'))
        ? CarbonImmutable::createFromFormat('!Y-m', request()->query('monat'))
        : $erster;
    $monat = $gewaehlt->lt($erster) ? $erster : ($gewaehlt->gt($erster->addMonths(11)) ? $erster->addMonths(11) : $gewaehlt);
    $sprache = app()->getLocale();

    $termine = \App\Support\Termine::imMonat($monat);
    $tageMitTermin = $termine->map(fn ($t) => $t['zeit']->day)->unique()->all();
    // In der Liste nur, was noch kommt
    $liste = $termine->filter(fn ($t) => $t['zeit']->gte($heute))->values();

    // Raster ab Montag: leere Felder vor dem Ersten
    $versatz = $monat->dayOfWeekIso - 1;
    $tage = $monat->daysInMonth;
    $wochentage = collect(range(0, 6))->map(fn ($i) => CarbonImmutable::create(2026, 10, 19)->addDays($i)->locale($sprache)->minDayName);

    $vorher = $monat->gt($erster) ? $monat->subMonth() : null;
    $nachher = $monat->lt($erster->addMonths(11)) ? $monat->addMonth() : null;
    $link = fn (CarbonImmutable $m) => '?monat='.$m->format('Y-m').'#kalender';

    $art = fn (string $a) => match ($a) {
        'selbsthilfegruppe' => __('Selbsthilfegruppe'),
        'arbeitsgruppe' => __('Arbeitsgruppe'),
        default => __('Veranstaltung'),
    };
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';
@endphp

<section id="kalender" aria-labelledby="kalender-titel" @class([
    'scroll-mt-24',
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12' => $rahmen,
    'bg-card border-y border-line' => $rahmen && $auf === 'card',
])>
    <div @class(['mx-auto max-w-6xl' => $rahmen])>
        <div @class(['max-w-md' => $rahmen])>
            <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
            <h2 id="kalender-titel" @class([
                'mb-4 font-display font-medium text-green',
                'text-2xl lg:text-3xl' => $rahmen,
                'text-xl lg:text-2xl' => ! $rahmen,
            ])>{{ $titel ?: __('Termine') }}</h2>

            <div class="rounded-card border border-line {{ $innen }} p-4 sm:p-5">
                <div class="mb-3 flex items-center justify-between gap-2">
                    @if ($vorher)
                        <a href="{{ $link($vorher) }}" class="rounded-full px-3 py-1.5 text-green-deep no-underline hover:bg-green-mist"
                           aria-label="{{ __('Vorheriger Monat: :monat', ['monat' => $vorher->locale($sprache)->isoFormat('MMMM YYYY')]) }}">‹</a>
                    @else
                        <span class="px-3 py-1.5" aria-hidden="true"></span>
                    @endif

                    <h3 class="font-display text-lg font-medium text-ink" aria-live="polite">
                        {{ $monat->locale($sprache)->isoFormat('MMMM YYYY') }}
                    </h3>

                    @if ($nachher)
                        <a href="{{ $link($nachher) }}" class="rounded-full px-3 py-1.5 text-green-deep no-underline hover:bg-green-mist"
                           aria-label="{{ __('Nächster Monat: :monat', ['monat' => $nachher->locale($sprache)->isoFormat('MMMM YYYY')]) }}">›</a>
                    @else
                        <span class="px-3 py-1.5" aria-hidden="true"></span>
                    @endif
                </div>

                {{-- Fürs Auge: Tage mit Termin grün. Vorlesehilfen lesen die Liste. --}}
                <div aria-hidden="true" class="grid grid-cols-7 gap-1 text-center text-sm">
                    @foreach ($wochentage as $tag)
                        <div class="pb-1 text-xs font-medium text-ink-soft">{{ $tag }}</div>
                    @endforeach
                    @for ($i = 0; $i < $versatz; $i++)
                        <div></div>
                    @endfor
                    @for ($t = 1; $t <= $tage; $t++)
                        @php
                            $datum = $monat->setDay($t);
                            $mitTermin = in_array($t, $tageMitTermin, true);
                            $vorbei = $datum->lt($heute);
                        @endphp
                        <div @class([
                            'mx-auto flex h-8 w-8 items-center justify-center rounded-full',
                            'bg-green font-semibold text-on-green' => $mitTermin && ! $vorbei,
                            'bg-green-mist text-ink-soft' => $mitTermin && $vorbei,
                            'text-ink-soft' => ! $mitTermin && $vorbei,
                            'text-ink' => ! $mitTermin && ! $vorbei,
                            'ring-2 ring-green ring-offset-1' => $datum->isSameDay($heute),
                        ])>{{ $t }}</div>
                    @endfor
                </div>
            </div>

            @if ($liste->isEmpty())
                <p class="mt-4 text-sm text-ink-soft">{{ __('In diesem Monat stehen keine weiteren Termine an.') }}</p>
            @else
                <ul class="mt-4 flex flex-col divide-y divide-line">
                    @foreach ($liste as $termin)
                        <li class="py-2.5">
                            <a href="{{ $termin['url'] }}" class="group block no-underline">
                                <time datetime="{{ $termin['zeit']->toIso8601String() }}" class="block text-sm text-ink-soft">
                                    {{ $termin['ganztaegig']
                                        ? $termin['zeit']->locale($sprache)->isoFormat('dd, D. MMMM')
                                        : __(':zeit Uhr', ['zeit' => $termin['zeit']->locale($sprache)->isoFormat($sprache === 'en' ? 'ddd, D MMMM, h:mm a' : 'dd, D. MMMM, HH:mm')]) }}
                                </time>
                                <span class="block font-medium text-ink group-hover:underline">{{ $termin['titel'] }}</span>
                                <span class="block text-xs text-ink-soft">{{ $art($termin['art']) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            <p class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                <a href="{{ sprachlink('events.index') }}" class="text-green-deep underline">{{ __('Alle Veranstaltungen') }}</a>
                <a href="{{ sprachlink('events.ical') }}" class="text-green-deep underline">{{ __('Termine in den eigenen Kalender') }}</a>
            </p>
        </div>
    </div>
</section>
