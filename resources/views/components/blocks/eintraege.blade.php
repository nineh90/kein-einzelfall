@props([
    'titel' => null,
    'einleitung' => null,
    // [['art' =>, 'datum' => 'Y-m-d', 'titel' =>, 'ort' =>, 'text' =>,
    //   'url' =>, 'link' =>, 'bild' =>, 'bild_alt' =>], ...]
    'eintraege' => [],
    'auf' => 'cream',  // cream | card
])

@php
    use App\Models\PageBlock;
    use Illuminate\Support\Carbon;

    // Ohne Titel raus, die neuesten zuerst (siehe PageBlock::eintraegeSortiert).
    $eintraege = PageBlock::eintraegeSortiert($eintraege);

    // Kästen stehen auf der jeweils anderen Fläche, sonst verschwämmen sie
    // auf der Karte mit dem Hintergrund.
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';

    // Ohne Überschrift des Bausteins hängen die Einträge direkt unter dem
    // Seitentitel (h1) und sind selbst Abschnitte (h2).
    $ebene = $titel ? 'h3' : 'h2';

    // Wie bei den Veranstaltungen: Auf Englisch der ausgeschriebene Monat,
    // „14.03.2026“ läse man dort leicht falsch herum.
    $datumFormat = app()->isLocale('en') ? 'j F Y' : 'd.m.Y';
    $datum = function (?string $wert) use ($datumFormat): ?Carbon {
        try {
            return filled($wert) ? Carbon::parse($wert) : null;
        } catch (\Throwable) {
            return null;
        }
    };
@endphp

@if ($eintraege !== [])
{{--
    Einträge mit Datum (KEV-129): Messen, Petitionen, Presseartikel,
    Interviews. Der Verein legt sie im Panel an, die Liste wächst mit.

    - Als Liste ausgezeichnet: Eine Vorlesehilfe sagt „Liste mit 5 Einträgen“
      an und kann von Eintrag zu Eintrag springen.
    - Das Bild ist optional. Ohne Bild bleibt eine Textkarte, damit der Verein
      einen Presseartikel eintragen kann, ohne erst ein Foto zu suchen.
    - Der Link nennt den Titel für Vorlesehilfen mit („Zum Artikel – …“),
      sonst stünden fünf gleiche „Mehr erfahren“ untereinander. Verweise
      nach draußen sind als solche gekennzeichnet. Kein target="_blank".
--}}
<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])
         @if ($titel) aria-labelledby="eintraege-{{ Str::slug($titel) }}" @endif>
    <div class="mx-auto max-w-6xl">

        @if ($titel || $einleitung)
            <div class="max-w-prose">
                @if ($titel)
                    <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
                    <h2 id="eintraege-{{ Str::slug($titel) }}"
                        class="mb-4 font-display text-2xl font-medium text-green lg:text-3xl">
                        {{ $titel }}
                    </h2>
                @endif

                @if ($einleitung)
                    <p class="mb-8 leading-relaxed text-ink-soft">{{ $einleitung }}</p>
                @endif
            </div>
        @endif

        <ul class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($eintraege as $eintrag)
                @php
                    $tag = $datum($eintrag['datum'] ?? null);
                    $art = PageBlock::EINTRAG_ARTEN[$eintrag['art'] ?? ''] ?? null;
                    $url = trim($eintrag['url'] ?? '');
                    $extern = (bool) preg_match('#^https?://#i', $url);
                    $zeilen = preg_split('/\R+/u', trim($eintrag['text'] ?? ''), flags: PREG_SPLIT_NO_EMPTY);
                @endphp
                <li>
                    <article class="flex h-full flex-col overflow-hidden rounded-card border border-line {{ $innen }}">
                        @if (filled($eintrag['bild'] ?? null))
                            <img src="{{ bildadresse($eintrag['bild']) }}"
                                 alt="{{ $eintrag['bild_alt'] ?? '' }}"
                                 loading="lazy"
                                 class="aspect-video w-full object-cover">
                        @endif

                        <div class="flex flex-1 flex-col p-5 lg:p-6">
                            <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-ink-soft">
                                @if ($art)
                                    <span class="rounded-full bg-green-mist px-2.5 py-0.5 text-[0.6875rem] text-green-deep">
                                        {{ __($art) }}
                                    </span>
                                @endif

                                @if ($tag)
                                    <time datetime="{{ $tag->toDateString() }}">
                                        {{ $tag->locale(app()->getLocale())->translatedFormat($datumFormat) }}
                                    </time>
                                @endif

                                @if (filled($eintrag['ort'] ?? null))
                                    <span aria-hidden="true">·</span>
                                    <span>{{ $eintrag['ort'] }}</span>
                                @endif
                            </p>

                            <{{ $ebene }} class="mt-2 font-display text-lg font-semibold leading-snug text-ink">
                                {{ $eintrag['titel'] }}
                            </{{ $ebene }}>

                            @if ($zeilen)
                                <div class="mt-2 flex flex-col gap-2 text-[0.9375rem] leading-relaxed text-ink-soft">
                                    @foreach ($zeilen as $zeile)
                                        <p>{{ hervorheben($zeile) }}</p>
                                    @endforeach
                                </div>
                            @endif

                            @if ($url !== '')
                                {{-- mt-auto: Die Links benachbarter Karten stehen auf einer Linie. --}}
                                <p class="mt-auto pt-4">
                                    <a href="{{ $url }}" @if ($extern) rel="noreferrer noopener" @endif
                                       class="inline-flex items-center gap-1.5 text-sm font-medium text-green-deep underline underline-offset-2 hover:text-green">
                                        {{ filled($eintrag['link'] ?? null) ? $eintrag['link'] : __('Mehr erfahren') }}
                                        <span class="sr-only">– {{ $eintrag['titel'] }}</span>
                                        @if ($extern)
                                            <span class="sr-only">{{ __('rahmen.partner.fremde_seite') }}</span>
                                        @endif
                                        <span aria-hidden="true">→</span>
                                    </a>
                                </p>
                            @endif
                        </div>
                    </article>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
