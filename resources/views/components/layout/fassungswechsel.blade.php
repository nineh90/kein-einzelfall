@props(['page' => null])

@php
    use App\Models\Page;

    $leicht = $page?->istLeichteSprache() ? null : $page?->leichteSprache();
    $schwer = $page?->istLeichteSprache() ? $page->standardfassung() : null;

    $ziel = $leicht ?? $schwer;
@endphp

{{--
    Wechsel zwischen schwerer Fassung und Leichter Sprache.

    Steht ganz oben und nicht am Seitenende: Wer Leichte Sprache braucht, soll
    nicht erst den schweren Text durchqueren müssen, um den Hinweis zu finden.

    Bewusst prominent und nicht als kleiner Textlink — dieselbe Überlegung wie
    beim Notausgang. Ein Angebot, das man suchen muss, ist für die Zielgruppe
    kein Angebot.

    Kein JavaScript: ein normaler Link auf eine eigene Adresse. Damit ist die
    Fassung verlinkbar, als Lesezeichen speicherbar und auffindbar — genau das,
    was BITV 2.0 § 4 verlangt und was ein aufklappbarer Kasten nicht leistet.
--}}
@if ($ziel)
    <div class="border-b border-line bg-green-mist px-4 py-3 lg:px-10">
        <div class="mx-auto flex max-w-6xl items-center gap-2.5">
            <x-ui.icon name="accessibility" :size="20" class="shrink-0 text-green-deep" />

            <a href="{{ $ziel->pfad() }}"
               class="font-medium text-green-deep underline underline-offset-2 hover:text-ink">
                {{ $leicht
                    ? __('rahmen.leichte_sprache.zu_leichter_sprache')
                    : __('rahmen.leichte_sprache.zur_standardfassung') }}
            </a>
        </div>
    </div>
@endif
