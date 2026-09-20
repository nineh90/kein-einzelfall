@props([
    'titel' => null,
    'einleitung' => null,
    'auf' => 'cream',    // cream | card
])

@php
    // Kästen stehen auf der jeweils anderen Fläche, sonst verschwämmen sie
    // auf der Karte mit dem Hintergrund.
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';
@endphp

@php
    $eintraege = config('speicher.eintraege', []);

    // Die Schlüsselnamen selbst stehen im Kopf der Seite (window.keSpeicher) —
    // dort braucht sie auch die Darstellungs-Toolbar. Hier steht nur, was
    // sichtbar ist.
    $anker = 'gespeicherte-einstellungen';
@endphp

{{--
    Übersicht über alles, was diese Website im Browser ablegt — und ein Weg,
    es wieder loszuwerden.

    ── Warum hier und nicht als Knopf im Fuss ──────────────────────────────────

    Die naheliegende Lösung wäre ein Fusszeilen-Link, der die Trigger-Warnung
    erneut öffnet. Sie hat zwei Haken: Man müsste ausgerechnet das Fenster
    aufrufen, das man abbestellt hat, um es wieder zu bestellen — und sie trägt
    genau einen Fall. Beim zweiten gespeicherten Wert bräuchte es einen zweiten
    Link, beim dritten einen dritten.

    Deshalb eine Liste, die sich aus `config/speicher.php` aufbaut: Was dort
    einmal eingetragen ist, erscheint hier von selbst und lässt sich einzeln
    oder gesammelt zurücksetzen. Der Fuss verweist mit einem Link hierher.

    ── Ohne JavaScript ─────────────────────────────────────────────────────────

    Dann stand hier nie etwas zum Zurücksetzen: Sowohl die Darstellungs-Toolbar
    als auch das Wegklicken der Trigger-Warnung brauchen JavaScript, um
    überhaupt etwas zu speichern. Die Erklärung bleibt trotzdem lesbar — sie
    gehört zur Auskunft darüber, was die Seite tut.
--}}
<section @class([
    'px-4 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
]) aria-labelledby="{{ $anker }}">
    <div class="mx-auto max-w-6xl">
        <div class="max-w-prose"
             data-speicher
             data-speicher-text-gespeichert="{{ __('rahmen.speicher.zustand_gespeichert') }}"
             data-speicher-text-leer="{{ __('rahmen.speicher.zustand_leer') }}"
             data-speicher-text-erledigt="{{ __('rahmen.speicher.zustand_erledigt') }}">

            <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>

            {{-- scroll-mt hält die Überschrift beim Anspringen aus dem Fuss
                 unter dem klebenden Kopfbereich sichtbar. --}}
            <h2 id="{{ $anker }}" class="mb-4 scroll-mt-24 font-display text-2xl font-medium text-ink lg:text-3xl">
                {{ $titel ?: __('rahmen.speicher.titel') }}
            </h2>

            <p class="mb-6 leading-relaxed text-ink-soft">
                {{ $einleitung ?: __('rahmen.speicher.einleitung') }}
            </p>

            <ul class="flex flex-col divide-y divide-line overflow-hidden rounded-card border border-line {{ $innen }}">
                @foreach ($eintraege as $eintrag)
                    <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                        data-speicher-eintrag="{{ $eintrag['schluessel'] }}">

                        <div class="sm:pe-6">
                            <p class="text-[0.9375rem] font-medium text-ink">{{ __($eintrag['label']) }}</p>
                            <p class="mt-0.5 text-sm text-ink-soft">{{ __($eintrag['text']) }}</p>

                            {{-- Der Zustand wird vom Skript eingetragen. Als
                                 aria-live, damit eine Vorlesehilfe die Änderung
                                 nach dem Zurücksetzen mitbekommt — sonst
                                 passiert für sie sichtbar nichts. --}}
                            <p class="mt-2 text-sm font-medium text-green-deep"
                               data-speicher-status
                               aria-live="polite"></p>
                        </div>

                        <x-ui.button type="button"
                                     variant="ghost"
                                     size="sm"
                                     class="shrink-0"
                                     data-speicher-loeschen="{{ $eintrag['schluessel'] }}"
                                     data-speicher-braucht-js>
                            {{ __('rahmen.speicher.zuruecksetzen') }}
                        </x-ui.button>
                    </li>
                @endforeach
            </ul>

            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
                <x-ui.button type="button"
                             class="w-full sm:w-auto"
                             data-speicher-alles
                             data-speicher-braucht-js>
                    {{ __('rahmen.speicher.alles') }}
                </x-ui.button>
            </div>

            {{-- Steht nur, solange das Skript nicht übernommen hat. Dasselbe
                 Muster wie bei der Trigger-Warnung, aber mit eigenem Merkmal:
                 Deren Klasse `ke-trigger-bereit` kommt nur, wenn der Dialog
                 auch gezeigt wird — wer ihn abbestellt hat, sah hier gar keine
                 Knöpfe. Ausgerechnet die Person, die sie braucht. --}}
            <p class="mt-4 text-sm text-ink-soft" data-speicher-ohne-js>
                {{ __('rahmen.speicher.ohne_js') }}
            </p>
        </div>
    </div>
</section>
