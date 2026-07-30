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

    // Ein Knopf ohne Beschriftung oder Ziel — siehe knoepfe() in helpers.php.
    $cta = knoepfe([$data['cta'] ?? null])[0] ?? null;
@endphp

@if (! $erlaubt || ! View::exists('components.'.$komponente))
    {{-- Still ignorieren, aber im Log vermerken: eine halb gerenderte Seite ist
         schlimmer als ein fehlender Block. --}}
    @php \Log::warning('Unbekannter Blocktyp', ['typ' => $block->typ, 'page_id' => $block->page_id]) @endphp

@elseif ($block->typ === 'text')
    {{-- Absätze als Daten statt als Slot: Nur so kann der Baustein selbst
         entscheiden, ab wo er einklappt. --}}
    <x-blocks.text
        :eyebrow="$data['eyebrow'] ?? null"
        :titel="$data['titel'] ?? null"
        :auf="$data['auf'] ?? $flaeche ?? 'cream'"
        :anker="$block->anker()"
        :absaetze="$data['absaetze'] ?? []"
        :hand="$data['hand'] ?? null"
        :cta="$cta" />

@elseif ($block->typ === 'hilfe_box')
    {{-- Die Hilfe-Box ist ein Kasten ohne eigene Seitenränder: Sie steht auch
         in der Fehlerseite und in engeren Spalten. Als eigenständiger Baustein
         auf einer Seite bekommt sie den Rahmen deshalb hier — sonst klebte sie
         am Bildschirmrand. --}}
    <div class="px-4 py-6 lg:px-10">
        <div class="mx-auto max-w-6xl">
            <x-blocks.hilfe-box
                :titel="$data['titel'] ?? 'Du brauchst jetzt Hilfe?'"
                :kompakt="(bool) ($data['kompakt'] ?? false)" />
        </div>
    </div>

@else
    <x-dynamic-component :component="$komponente" :attributes="new \Illuminate\View\ComponentAttributeBag($data)" />
@endif
