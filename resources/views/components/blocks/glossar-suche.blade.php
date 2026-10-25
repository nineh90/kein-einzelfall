@props([
    'seite' => null,   // die Seite, auf der der Baustein steht
    'titel' => null,
    'auf' => 'cream',  // cream | card
])

@php
    use App\Models\Language;
    use App\Support\KlartextSuche;

    $suche = mb_substr(trim((string) request()->query('suche', '')), 0, KlartextSuche::HOECHSTLAENGE);
    $gruppen = KlartextSuche::suchen($suche);
    $anzahl = collect($gruppen)->sum(fn ($g) => count($g['begriffe']) + count($g['saetze']));

    $hier = request()->getPathInfo();
    $suchPfad = Language::aktuell()->pfad('/suche');
@endphp

{{--
    Suche über alle Kategorien unter „Klartext statt Fachsprache“ (KEV-254).

    Gefiltert wird beim Tippen wie im einzelnen Glossar (Kevin: einheitlich).
    Die Treffer rechnet aber der Server aus: Alle Kategorien zusammen sind
    über 700 Einträge, die müsste jeder Besuch sonst vorab laden.
    resources/js/klartext-suche.js holt diese Seite mit ?suche= und tauscht
    Trefferzahl und Liste aus. Ohne JavaScript schickt das Formular ab und
    springt zu den Treffern (#klartext-suche).

    Die Treffer stehen je Kategorie, jeder mit Link auf seine Stelle in der
    Kategorie: Begriffe auf ihr Sprungziel, Sätze auf die typischen Sätze.
--}}
<section id="klartext-suche" aria-labelledby="klartext-suche-titel" @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div class="mx-auto max-w-6xl">
        <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
        <h2 id="klartext-suche-titel" class="mb-4 font-display text-2xl font-medium text-green lg:text-3xl">
            {{ $titel ?: __('In allen Kategorien suchen') }}
        </h2>

        <form role="search" method="get" action="{{ $hier }}#klartext-suche" class="max-w-xl" data-klartext-suche>
            <label for="klartext-suchfeld" class="mb-2 block text-ink-soft">
                {{ __('Abkürzung, Begriff oder Satz aus deinem Schreiben') }}
            </label>

            <div class="flex flex-col gap-3 sm:flex-row">
                <input type="search"
                       id="klartext-suchfeld"
                       name="suche"
                       value="{{ $suche }}"
                       maxlength="{{ KlartextSuche::HOECHSTLAENGE }}"
                       autocomplete="off"
                       spellcheck="false"
                       class="w-full rounded-full border border-line bg-cream px-5 py-3 text-ink
                              placeholder:text-ink-soft focus:border-green focus:outline-none">

                <x-ui.button type="submit" variant="primary" class="shrink-0">{{ __('Suchen') }}</x-ui.button>
            </div>
        </form>

        {{-- Trefferzahl als eigene, immer vorhandene Ansage: Beim Tippen
             tauscht das Skript nur ihren Text, Vorlesehilfen lesen dann die
             Zahl vor und nicht die ganze Liste. --}}
        <p class="mt-6 text-ink-soft" aria-live="polite" data-klartext-status>@if ($suche !== '' && $anzahl > 0){{ __('Treffer: :anzahl (in :kategorien von :gesamt Kategorien)', [
                'anzahl' => $anzahl,
                'kategorien' => count($gruppen),
                'gesamt' => count(KlartextSuche::kategorien()),
            ]) }}@elseif ($suche !== ''){{ __('Zu „:suche“ steht in keiner Kategorie etwas.', ['suche' => $suche]) }}@endif</p>

        <div data-klartext-treffer>
        @if ($suche !== '')
            <p class="mt-2">
                @if ($anzahl === 0)
                    <a href="{{ $suchPfad }}?q={{ urlencode($suche) }}" class="text-green-deep underline">{{ __('Auf der ganzen Website suchen') }}</a>
                @else
                    <a href="{{ $hier }}" class="text-green-deep underline" data-klartext-leeren>{{ __('Suche leeren') }}</a>
                @endif
            </p>
            @foreach ($gruppen as $gruppe)
                <section class="mt-8" aria-label="{{ $gruppe['titel'] }}">
                    <h3 class="mb-2 border-b border-line pb-2 font-display text-xl font-medium text-green-deep">
                        <a href="{{ $gruppe['url'] }}" class="no-underline hover:underline">{{ $gruppe['titel'] }}</a>
                        <span class="ms-1 text-base font-normal text-ink-soft">({{ count($gruppe['begriffe']) + count($gruppe['saetze']) }})</span>
                    </h3>

                    <dl class="md:columns-2 md:gap-10">
                        @foreach ($gruppe['begriffe'] as $eintrag)
                            <div class="break-inside-avoid border-b border-line py-2.5">
                                <dt class="font-display font-semibold text-ink">
                                    <a href="{{ $gruppe['url'] }}#{{ $eintrag->slug }}" class="text-ink underline decoration-line hover:decoration-green">{{ $eintrag->ueberschrift() }}</a>
                                </dt>
                                @if ($eintrag->erklaerung)
                                    <dd class="mt-1 hyphens-auto text-[0.9375rem] leading-relaxed text-ink-soft">{{ $eintrag->erklaerung }}</dd>
                                @endif
                            </div>
                        @endforeach

                        @foreach ($gruppe['saetze'] as $satz)
                            <div class="break-inside-avoid border-b border-line py-2.5">
                                <dt @unless (Language::aktuell()->istStandard()) lang="de" @endunless class="font-display font-semibold text-ink">
                                    <a href="{{ $gruppe['url'] }}#typische-saetze" class="text-ink underline decoration-line hover:decoration-green">„{{ $satz['satz'] ?? '' }}“</a>
                                </dt>
                                <dd class="mt-1 hyphens-auto text-[0.9375rem] leading-relaxed text-ink-soft">
                                    <span class="font-medium text-green-deep">{{ __('Einfach übersetzt:') }}</span>
                                    {{ $satz['bedeutung'] ?? '' }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endforeach
        @endif
        </div>
    </div>
</section>
