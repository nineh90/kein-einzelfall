@props(['krumen' => []])

@if (count($krumen) > 1)
    {{-- Orientierung: Wo bin ich, und wie komme ich eine Ebene zurück?
         Der letzte Eintrag ist die aktuelle Seite und deshalb kein Link. --}}
    <nav aria-label="Sie sind hier" class="mb-4">
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-ink-soft">
            @foreach ($krumen as $krume)
                <li class="flex items-center gap-2">
                    @if (! $loop->first)
                        <span aria-hidden="true" class="text-line">›</span>
                    @endif

                    @if ($krume['url'] && ! $loop->last)
                        <a href="{{ $krume['url'] }}"
                           class="text-ink-soft no-underline hover:text-green-deep hover:underline">
                            {{ $krume['label'] }}
                        </a>
                    @else
                        <span aria-current="page" class="text-ink">{{ $krume['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
