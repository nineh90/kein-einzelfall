{{-- Vorschaubild eines Bereichs im Handy-Menü. Ohne Bild ein ruhiges Feld,
     damit die Zeilen gleich hoch bleiben. Schmuck, deshalb alt="". Beim
     Vereinsbild liegt wie im Seitenkopf das Logo obenauf (KEV-75). --}}
@if ($daumen)
    <span class="relative h-14 w-14 shrink-0" aria-hidden="true">
        <img src="{{ bildadresse($daumen) }}" alt="" loading="lazy"
             class="h-14 w-14 rounded-xl border border-line object-cover"
             style="object-position: {{ \App\Support\Titelbilder::fokus($bild) }}">
        @if (\App\Support\Titelbilder::logoDarauf($bild))
            <img src="{{ bildadresse('/img/logo-gross.webp') }}" alt="" loading="lazy" class="absolute inset-0 m-auto h-9 w-auto">
        @endif
    </span>
@else
    <span class="h-14 w-14 shrink-0 rounded-xl border border-line bg-green-mist" aria-hidden="true"></span>
@endif
