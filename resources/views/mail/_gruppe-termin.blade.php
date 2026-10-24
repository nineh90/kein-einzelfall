{{-- Termin und Ort einer Gruppe für die Textmails (KEV-226). Reiner Text: {!! !!}. --}}
@php
    $naechster = $gruppe->naechsterTermin();
    $format = app()->isLocale('en') ? 'l, j F Y, g:i a' : 'l, j. F Y, H:i';
@endphp
@if ($gruppe->rhythmus)
{!! __('Wann') !!}: {!! $gruppe->rhythmus !!}@if ($gruppe->uhrzeit) {!! __('um :uhrzeit', ['uhrzeit' => $gruppe->uhrzeit]) !!}@endif

@endif
@if ($naechster)
{!! __('Nächster Termin') !!}: {!! __(':zeit Uhr', ['zeit' => $naechster->locale(app()->getLocale())->translatedFormat($format)]) !!}
@endif
@if ($gruppe->ort || $gruppe->online)
{!! __('Wo') !!}: {!! \Illuminate\Support\Str::ucfirst($gruppe->ort ?: __('online')) !!}
@endif
