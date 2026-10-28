@props(['krumen' => []])

@if (count($krumen) > 1)
    {{-- Orientierung: Wo bin ich, und wie komme ich eine Ebene zurück?
         Der letzte Eintrag ist die aktuelle Seite und deshalb kein Link. --}}
    <nav aria-label="{{ __('rahmen.sie_sind_hier') }}" class="mb-4">
        {{-- py-1 an den Links: Trefferfläche mindestens 24 px hoch (WCAG 2.5.8).
             Mit dem Finger sind die Krumen sonst kaum zu treffen. --}}
        <ol class="flex flex-wrap items-center gap-x-2 text-sm text-ink-soft">
            @foreach ($krumen as $krume)
                <li class="flex items-center gap-2">
                    @if (! $loop->first)
                        <span aria-hidden="true" class="text-line">›</span>
                    @endif

                    @if ($krume['url'] && ! $loop->last)
                        <a href="{{ $krume['url'] }}"
                           class="inline-block py-1 text-ink-soft no-underline hover:text-green-deep hover:underline">
                            {{ $krume['label'] }}
                        </a>
                    @else
                        <span aria-current="page" class="text-ink">{{ $krume['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

    {{-- Dieselben Krumen für Suchmaschinen (SEO-Prüfung 28.10.2026): Google
         zeigt dann „Start › Wissen › …“ statt der nackten Adresse. Der letzte
         Eintrag ist die Seite selbst. --}}
    @php
        $liste = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($krumen)->values()->map(fn ($krume, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $krume['label'],
                'item' => $krume['url'] && $i < count($krumen) - 1 ? url($krume['url']) : url()->current(),
            ])->all(),
        ];
    @endphp
    <script type="application/ld+json" @isset($cspNonce) nonce="{{ $cspNonce }}" @endisset>
        {!! json_encode($liste, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
    </script>
@endif
