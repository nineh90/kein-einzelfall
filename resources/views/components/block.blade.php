@props([
    'block',
    // Vorgabe der Seite, damit aufeinanderfolgende Bausteine die Fläche
    // wechseln — berechnet mit PageBlock::flaechenFuer(), das eine im
    // Baustein hinterlegte Fläche bereits berücksichtigt.
    'flaeche' => null,
])

@php
    use App\Models\Page;
    use App\Models\PageBlock;

    // Nur bekannte Typen rendern. Ein Blocktyp aus der Datenbank darf niemals
    // eine beliebige Blade-Komponente einbinden können.
    $erlaubt = array_key_exists($block->typ, PageBlock::TYPEN);
    $komponente = $block->komponente();
    $data = $block->data ?? [];

    // Ein Knopf ohne Beschriftung oder Ziel — siehe knoepfe() in helpers.php.
    $cta = knoepfe([$data['cta'] ?? null])[0] ?? null;

    // Ohne Vorgabe (etwa in der Trigger-Warnung) gilt die helle Seitenfläche.
    $auf = $flaeche ?? $data['auf'] ?? 'cream';
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
        :auf="$auf"
        :anker="$block->anker()"
        :absaetze="$data['absaetze'] ?? []"
        :hand="$data['hand'] ?? null"
        :mittig="(bool) ($data['mittig'] ?? false)"
        :ab_absatz="$block->textAngaben()['ab_absatz']"
        :symbol="$data['symbol'] ?? null"
        :cta="$cta" />

@elseif ($block->typ === 'hilfe_box')
    {{-- Die Hilfe-Box ist ein Kasten ohne eigene Seitenränder: Sie steht auch
         in der Fehlerseite und in engeren Spalten. Als eigenständiger Baustein
         auf einer Seite bekommt sie den Rahmen deshalb hier — sonst klebte sie
         am Bildschirmrand. --}}
    <div @class([
        'px-4 md:px-8 py-6 lg:px-10',
        'bg-card border-y border-line' => $auf === 'card',
    ])>
        <div class="mx-auto max-w-6xl">
            <x-blocks.hilfe-box
                :titel="$data['titel'] ?? 'Du brauchst jetzt Hilfe?'"
                :kompakt="(bool) ($data['kompakt'] ?? false)" />
        </div>
    </div>

@else
    {{-- Die Fläche nur an Bausteine geben, die sie als Prop kennen — bei
         allen anderen landete sie als Attribut auf-="…" im HTML. --}}
    @php
        $attribute = $block->nimmtFlaeche() ? ['auf' => $auf] + $data : $data;

        // Kontaktabschluss auf einer Seite mit Aufmacher: Dessen Band trägt
        // die Vertrauenssignale schon (KEV-30).
        if ($block->typ === 'contact_close' && $block->page?->blocks->contains('typ', 'hero')) {
            $attribute['vertrauen'] = false;
        }

        // Der kurze Strich markiert auf den Unterseiten jeden Abschnittsanfang.
        // Auf der Startseite tragen die Abschnitte keinen, „Jetzt spenden“
        // stand damit allein da und sah nach Versehen aus (KEV-170, Taddi).
        // Auf der Vereinsseite genauso (KEV-175): derselbe Kasten wie auf
        // der Startseite, und auch dort hat sonst kein Abschnitt einen Strich.
        if ($block->typ === 'donation_options' && in_array($block->page?->slug, [Page::STARTSEITE_SLUG, 'verein'], true)) {
            $attribute['strich'] = false;
        }
        // Ebenso der Termin-Kalender auf der Startseite (KEV-281).
        if ($block->typ === 'kalender' && $block->page?->slug === Page::STARTSEITE_SLUG) {
            $attribute['strich'] = false;
        }

        // Die Kacheln (KEV-156) zeigen die Seiten unter dieser Seite, das
        // Glossar (KEV-155) die Einträge dieser Seite. Beide müssen deshalb
        // wissen, wo sie stehen.
        if (in_array($block->typ, ['unterseiten', 'glossar', 'glossar_suche'], true)) {
            $attribute['seite'] = $block->page;
        }

        // Schritte und Text mit Bild mit Sprungziel, wie die Textabschnitte
        // (KEV-152: die Kachel „Frist bekommen – Was jetzt?“ springt dorthin).
        // Die typischen Sätze (KEV-262) springt das Glossar darüber an.
        if (in_array($block->typ, ['schritte', 'text_media', 'typische_saetze'], true)) {
            $attribute['anker'] = $block->anker();
        }
    @endphp
    <x-dynamic-component :component="$komponente" :attributes="new \Illuminate\View\ComponentAttributeBag($attribute)" />
@endif
