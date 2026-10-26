@props(['person'])

{{-- Rechtsbündig unter dem Text einer Person, mit Gedankenstrich (KEV-183,
     Taddi: „am Ende immer rechts unter dem Text … den jeweiligen Namen“). --}}
<p {{ $attributes->class('text-right text-sm text-ink') }}>– {{ $person->unterschrift() }}</p>
