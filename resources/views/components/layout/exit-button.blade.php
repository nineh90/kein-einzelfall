@props(['variant' => 'header'])

{{--
    Immer ein echtes <a> mit href: ohne JavaScript ist das ein normaler Link,
    der trotzdem wegführt. Das Script oben fängt den Klick ab und macht daraus
    ein location.replace(), damit der aktuelle History-Eintrag ersetzt wird.
--}}
<a
    href="{{ config('navigation.exit_url') }}"
    data-notausgang
    rel="noreferrer noopener"
    @class([
        'inline-flex items-center gap-2 no-underline transition-colors',
        // Header: dunkelgrüne Pille, ruhig — kein Alarmrot im Sichtfeld
        'rounded-full bg-green-deep px-4 py-2 text-sm text-on-green hover:bg-ink'
            => $variant === 'header',
        // Mobile-Bar: der einzige Ort, an dem die Warnfarbe eingesetzt wird
        'flex-col justify-center gap-1 px-2 py-2 text-[0.6875rem] text-alert'
            => $variant === 'bar',
    ])
>
    <x-ui.icon name="exit" :size="$variant === 'bar' ? 22 : 14" />
    <span>{{ $variant === 'bar' ? 'Exit' : 'Notausgang' }}</span>
    <span class="sr-only">– verlässt diese Seite sofort</span>
</a>
