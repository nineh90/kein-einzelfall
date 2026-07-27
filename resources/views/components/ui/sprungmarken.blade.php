@props(['punkte' => [], 'titel' => 'Auf dieser Seite'])

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

        <ol class="flex flex-col gap-1.5">
            @foreach ($punkte as $punkt)
                <li class="flex gap-2.5 text-sm">
                    <span class="select-none text-ink-soft" aria-hidden="true">{{ $loop->iteration }}.</span>
                    <a href="#{{ $punkt['anker'] }}"
                       class="text-green-deep no-underline hover:underline">
                        {{ $punkt['titel'] }}
                    </a>
                </li>
            @endforeach
        </ol>
    </nav>
@endif
