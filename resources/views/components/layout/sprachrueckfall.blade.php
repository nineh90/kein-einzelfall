@props(['quelle' => null])

@php
    use App\Models\Language;

    $ziel = Language::aktuell();
@endphp

{{--
    Sichtbarer Hinweis, dass diese Seite noch nicht übersetzt ist.

    Das ist kein Notbehelf, sondern die inhaltliche Grenze des Auftrags:
    Es geht um Opferrechte, Fristen und Notfallnummern. Eine unscharfe oder
    unbemerkt fremdsprachige Seite kann hier realen Schaden anrichten. Wer
    liest, muss wissen, dass er die deutsche Fassung vor sich hat.

    Kein Alarmton, keine Warnfarbe: Die Seite ist ja nutzbar. Ein ruhiger
    Hinweis, den man wahrnimmt und dann beiseitelegt.

    role="status" statt "alert": Vorlesehilfen sollen ihn ankündigen, aber
    niemanden aus dem Lesefluss reissen.
--}}
@if ($quelle && $quelle->code !== $ziel->code)
    {{-- Eigenes lang: Der Hinweis ist in der gewählten Sprache, der Inhalt
         darunter nicht. Ohne das läse ihn eine Vorlesehilfe falsch aus. --}}
    <div lang="{{ $ziel->code }}" dir="{{ $ziel->richtung }}"
         class="border-b border-line bg-green-mist px-4 py-3 lg:px-10">
        <p role="status"
           class="mx-auto flex max-w-6xl items-start gap-2.5 text-sm text-ink">
            <x-ui.icon name="info" :size="18" class="mt-0.5 shrink-0 text-green-deep" />
            <span>
                {{ __('rahmen.rueckfall.hinweis', [
                    'ziel' => $ziel->label,
                    'quelle' => $quelle->label,
                ]) }}
            </span>
        </p>
    </div>
@endif
