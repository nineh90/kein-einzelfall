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
        'inline-flex items-center justify-center no-underline transition-colors',
        // Header: dunkelgrüne Pille, ruhig — kein Alarmrot im Sichtfeld.
        // Auf schmalen Geräten nur das Symbol als 40-px-Kreis (voller Tap),
        // ab „sm“ die Pille mit Beschriftung. Bis KEV-26 schon ab 380 px —
        // dann blieb dem Vereinsnamen daneben kein Platz, und er wurde auf
        // jedem gängigen Handy abgeschnitten. Unterhalb von „lg“ steht der
        // Notausgang ausserdem beschriftet in der unteren Leiste.
        'gap-2 rounded-full bg-green-deep text-sm text-on-green hover:bg-ink'
            .' h-10 w-10 sm:h-auto sm:w-auto sm:px-4 sm:py-2'
            => $variant === 'header',
        // Mobile-Bar: der einzige Ort, an dem die Warnfarbe eingesetzt wird
        'flex-col gap-1 px-2 py-2 text-[0.6875rem] text-alert'
            => $variant === 'bar',
    ])
>
    <x-ui.icon name="exit" :size="$variant === 'bar' ? 22 : 18" />
    {{-- Beschriftung im Kopf erst ab „sm“ — darunter trägt das Symbol allein,
         und die Vorlesehilfe bekommt den Namen weiterhin aus dem sr-only-Text. --}}
    <span @class(['hidden sm:inline' => $variant === 'header'])>{{ $variant === 'bar' ? __('rahmen.notausgang.leiste') : __('rahmen.notausgang.kopf') }}</span>
    <span class="sr-only">{{ __('rahmen.notausgang.erklaerung') }}</span>
</a>
