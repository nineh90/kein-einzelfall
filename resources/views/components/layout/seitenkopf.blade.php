@props([
    'titel',
    'bereich' => null,
    'krumen' => [],
    'lead' => null,
])

{{--
    Seitenkopf.

    Ohne ihn beginnt jede Unterseite mit einer nackten Überschrift auf leerer
    Fläche — die Startseite hat einen Aufmacher, die Unterseiten wirkten dagegen
    unfertig. Der Kopf gibt ihnen denselben ruhigen Einstieg, ohne dass dafür
    Inhalte erfunden werden müssten: Bereich und Brotkrumen stammen aus der
    Navigation, der Vorspann ist der erste Absatz der Seite.
--}}
<header class="relative overflow-hidden border-b border-line bg-card px-4 pb-8 pt-6 lg:px-10 lg:pb-12 lg:pt-8">

    {{-- Zurückhaltende Fläche im Hintergrund. Dieselbe Bildsprache wie der
         Aufmacher der Startseite, nur deutlich leiser. --}}
    <div aria-hidden="true"
         class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full
                bg-[radial-gradient(circle,rgb(220_230_219/.55)_0%,transparent_70%)]"></div>

    <div class="relative mx-auto max-w-6xl">
        <x-ui.brotkrumen :krumen="$krumen" />

        @if ($bereich)
            <x-ui.eyebrow class="mb-3">{{ $bereich }}</x-ui.eyebrow>
        @endif

        <h1 class="max-w-3xl font-display text-[1.75rem] font-medium leading-tight text-ink lg:text-[2.5rem]">
            {{ $titel }}
        </h1>

        @if ($lead)
            {{-- Erster Absatz größer gesetzt: gibt der Seite einen Einstieg und
                 hilft beim Einordnen, bevor der Fließtext beginnt. --}}
            <p class="mt-4 max-w-prose text-lg leading-relaxed text-ink-soft">
                {{ $lead }}
            </p>
        @endif
    </div>
</header>
