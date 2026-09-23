@props(['punkte' => [], 'titel' => null])

@php $titel ??= __('rahmen.auf_dieser_seite'); @endphp

@if (count($punkte) >= 4)
    {{--
        Inhaltsverzeichnis für lange Seiten.

        Erscheint automatisch ab vier Abschnitten — wer sich durch 1.600 Wörter
        Datenschutzerklärung sucht, soll springen können. Für Menschen mit
        Konzentrationsschwierigkeiten ist das kein Komfort, sondern Zugang.

        Als <nav> mit eigenem Label, damit Screenreader es überspringen oder
        gezielt anspringen können.
    --}}
    <nav aria-label="{{ $titel }}"
         class="rounded-card border border-line bg-card px-5 py-4">
        <p class="mb-3 font-display text-base font-medium text-ink">{{ $titel }}</p>

        {{-- Der Abstand steckt im Link (py-1) statt zwischen den Einträgen:
             Gleiches Bild, aber die Trefferfläche wird mindestens 24 px hoch. --}}
        <ol class="flex flex-col">
            @foreach ($punkte as $punkt)
                <li class="flex gap-2.5 text-sm">
                    <span class="select-none py-1 text-ink-soft" aria-hidden="true">{{ $loop->iteration }}.</span>
                    <a href="#{{ $punkt['anker'] }}"
                       class="py-1 text-green-deep no-underline hover:underline">
                        {{ $punkt['titel'] }}
                    </a>
                </li>
            @endforeach
        </ol>
    </nav>
@endif
