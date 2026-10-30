{{-- Startseite des Panels (App\Filament\Widgets\WasWartet). Inline-Stile,
     weil das Panel Filaments eigenes CSS nutzt und unsere Tailwind-Klassen
     dort nicht ankommen. --}}
<x-filament-widgets::widget>
    <div style="display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(20rem, 1fr));">
        @foreach ($kaesten as $kasten)
            <x-filament::section :heading="$kasten['titel']">
                <p style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;
                          color: {{ $kasten['anzahl'] > 0 ? 'rgb(180 83 9)' : 'inherit' }};">
                    {{ match (true) {
                        $kasten['anzahl'] === 0 => $kasten['keine'],
                        $kasten['anzahl'] === 1 => $kasten['eine'],
                        default => str_replace(':anzahl', $kasten['anzahl'], $kasten['mehrere']),
                    } }}
                </p>

                <x-filament::button tag="a" :href="$kasten['url']" size="lg"
                                    :color="$kasten['anzahl'] > 0 ? 'primary' : 'gray'">
                    {{ $kasten['knopf'] }}
                </x-filament::button>

                <p style="margin-top: 1rem; font-size: 0.875rem; opacity: 0.75;">{{ $kasten['hilfe'] }}</p>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-widgets::widget>
