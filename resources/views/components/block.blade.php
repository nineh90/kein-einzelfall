@props([
    'block',
    // Vorgabe der Seite, damit aufeinanderfolgende Textbausteine die Fläche
    // wechseln können. Eine im Baustein hinterlegte Fläche hat Vorrang.
    'flaeche' => null,
])

@php
    use App\Models\PageBlock;

    // Nur bekannte Typen rendern. Ein Blocktyp aus der Datenbank darf niemals
    // eine beliebige Blade-Komponente einbinden können.
    $erlaubt = array_key_exists($block->typ, PageBlock::TYPEN);
    $komponente = $block->komponente();
    $data = $block->data ?? [];
@endphp

@if (! $erlaubt || ! View::exists('components.'.$komponente))
    {{-- Still ignorieren, aber im Log vermerken: eine halb gerenderte Seite ist
         schlimmer als ein fehlender Block. --}}
    @php \Log::warning('Unbekannter Blocktyp', ['typ' => $block->typ, 'page_id' => $block->page_id]) @endphp

@elseif ($block->typ === 'text')
    {{-- Text ist der einzige Typ mit Fließtext-Inhalt: die Absätze gehen als
         Slot hinein, nicht als Attribut. --}}
    <x-blocks.text
        :titel="$data['titel'] ?? null"
        :auf="$data['auf'] ?? $flaeche ?? 'cream'"
        :anker="$block->anker()">
        @foreach ($data['absaetze'] ?? [] as $absatz)
            <p>{{ $absatz }}</p>
        @endforeach
    </x-blocks.text>

@else
    <x-dynamic-component :component="$komponente" :attributes="new \Illuminate\View\ComponentAttributeBag($data)" />
@endif
