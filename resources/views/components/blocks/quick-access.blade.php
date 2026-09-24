@props([
    'titel' => 'Wie wir helfen',
    'sub' => null,
    'karten' => [],   // [['icon'=>, 'titel'=>, 'text'=>, 'url'=>, 'link'=>], ...]
    'auf' => 'cream', // cream | card
])

@php
    // Kästen stehen auf der jeweils anderen Fläche, sonst verschwämmen sie
    // auf der Karte mit dem Hintergrund.
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';
@endphp

@php
    // Die ganze Karte ist ein Link. Eine Karte ohne Ziel wäre damit ein Link
    // ins Leere — im Panel entsteht so etwas, sobald jemand eine Karte anlegt
    // und nicht fertig ausfüllt. Hier fällt sie raus, statt als toter Kasten
    // auf der Seite zu landen.
    $karten = array_values(array_filter(
        $karten,
        fn ($karte) => filled($karte['titel'] ?? null) && filled($karte['url'] ?? null),
    ));
@endphp

<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
]) aria-labelledby="qa-titel">
    <div class="mx-auto max-w-6xl">
        <x-ui.section-head :titel="$titel" :sub="$sub" />

        {{-- Auf dem Handy einspaltig, ab „sm“ zwei, ab „lg“ vier Spalten.

             Das Mockup wollte mobil zwei Spalten. Mit den echten Texten ging
             das nicht (KEV-33): Bei 390 und 414 px war eine Spalte 170 px
             schmal, „Schwerbehindertenausweis“ und „Informationsplattform“
             passten nicht hinein, drückten die Karte breiter und schoben sie
             über die Nachbarin. Und selbst ohne das standen drei Wörter in
             einer Zeile. Eine Spalte liest sich dort besser.

             Zusätzlich gesichert, falls im Panel noch längere Wörter
             kommen: minmax(0,1fr) und min-w-0 lassen keine Karte breiter
             als ihre Spalte werden, hyphens-auto trennt lange Wörter (die
             Seite trägt lang="de"), break-words bricht notfalls hart um.
             hyphenate-limit-chars beschränkt das Trennen auf Wörter ab 14
             Zeichen: Sonst trennte der Browser auch „ge-zielt“, und der Text
             sah zerhackt aus. Browser ohne die Eigenschaft trennen eben
             großzügiger, überlaufen tut trotzdem nichts. --}}
        <ul class="grid grid-cols-[minmax(0,1fr)] gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:gap-4">
            @foreach ($karten as $karte)
                <li class="flex min-w-0">
                    {{-- Die ganze Karte ist der Link — größere Trefferfläche,
                         nur ein Tab-Stopp statt zwei. --}}
                    <a href="{{ $karte['url'] }}"
                       class="group flex min-w-0 flex-1 flex-col rounded-card border border-line {{ $innen }}
                              p-4 hyphens-auto break-words [hyphenate-limit-chars:14_6_6] no-underline hover:border-green lg:p-5">
                        <span class="mb-3 text-green">
                            <x-ui.icon :name="$karte['icon']" :size="28" />
                        </span>
                        <span class="font-display text-base font-semibold text-ink group-hover:underline">
                            {{ $karte['titel'] }}
                        </span>
                        @if (!empty($karte['text']))
                            <span class="mt-1.5 flex-1 text-[0.8125rem] leading-relaxed text-ink-soft">
                                {{ $karte['text'] }}
                            </span>
                        @endif
                        <span class="mt-3 text-[0.8125rem] text-green-deep">
                            {{ $karte['link'] ?? 'Mehr erfahren' }} <span aria-hidden="true">→</span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
