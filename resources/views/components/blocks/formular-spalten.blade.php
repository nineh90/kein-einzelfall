@props([
    // list<array{titel?: string, absaetze?: list<string>, hand?: string, knopf?: string, art?: string}>
    'spalten' => [],
    'auf' => 'cream',      // cream | card
])

@php
    use App\Http\Requests\BeschwerdeRequest;

    $spalten = collect($spalten)->values()->map(fn ($spalte) => $spalte + [
        'art' => in_array($spalte['art'] ?? null, BeschwerdeRequest::WEGE, true) ? $spalte['art'] : 'anfrage',
    ]);

    // Ein Formular für beide Wege. Die Kennung trägt Feld-IDs und das
    // Sprungziel nach dem Absenden.
    $kennung = 'beschwerde';

    // Die Wege zur Auswahl, benannt wie die Spalten darüber.
    $wege = $spalten
        ->map(fn ($s) => ['wert' => $s['art'], 'titel' => $s['titel'] ?? ($s['art'] === 'ombudsstelle' ? 'Beschwerde' : 'Kritik')])
        ->unique('wert')
        ->values()
        ->all();
@endphp

{{--
    Zwei Wege nebeneinander, darunter ein gemeinsames Formular (KEV-98,
    Beschwerdemanagement: links Kritik von außen, rechts Beschwerden über den
    Verein).

    Zuerst waren es zwei gleiche Formulare, je eins unter jedem Text. Das sah
    doppelt aus (Kevin, 07.10.2026). Jetzt wählt man im Formular oben, worum
    es geht. Der Knopf unter jedem Text springt zum Formular und setzt die
    Wahl über die Adresse vor (?weg=…), ganz ohne JavaScript.

    Ab „md“ stehen die Texte nebeneinander, die Knöpfe unten auf einer Linie.

    Welcher Weg gewählt wird, entscheidet, wohin die Nachricht geht:
    `anfrage` in den Verwaltungsbereich, `ombudsstelle` per E-Mail an die
    unabhängige Stelle, ohne dass der Verein sie zu sehen bekommt. Siehe
    BeschwerdeController.
--}}
<section @class([
    'px-4 md:px-8 py-10 lg:px-10 lg:py-16',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div class="mx-auto grid max-w-6xl grid-cols-[minmax(0,1fr)] md:grid-cols-2 md:gap-x-16 lg:gap-x-24">
        @foreach ($spalten as $spalte)
            <x-blocks.text-inhalt
                :fuellen="true"
                :class="$loop->first ? '' : 'mt-10 border-t border-line pt-10 md:mt-0 md:border-t-0 md:pt-0'"
                :titel="$spalte['titel'] ?? null"
                :anker="filled($spalte['titel'] ?? null) ? 'abschnitt-'.\Illuminate\Support\Str::slug($spalte['titel']) : null"
                :absaetze="$spalte['absaetze'] ?? []"
                :ab_absatz="99"
                :hand="$spalte['hand'] ?? null"
                :cta="[
                    'label' => $spalte['knopf'] ?? 'Zum Formular',
                    'url' => '?weg='.$spalte['art'].'#formular-'.$kennung,
                    'variant' => 'primary',
                ]" />
        @endforeach
    </div>

    <div id="formular-{{ $kennung }}"
         class="mx-auto mt-14 max-w-3xl scroll-mt-24 border-t border-line pt-12 lg:mt-20">
        <h2 class="mb-2 font-display text-2xl font-medium text-green lg:text-3xl">Schreib uns</h2>

        <x-ui.nachricht-formular
            :wege="$wege"
            :kennung="$kennung"
            :herkunft="request()->path()"
            :auf="$auf" />
    </div>
</section>
