@props([
    'titel' => 'Schreib uns',
    'herkunft' => null,
    'auf' => 'cream',    // cream | card
])

{{--
    Kontaktformular als eigener Abschnitt. Die Felder stehen in
    x-ui.nachricht-formular, damit sie auch in den zwei Spalten des
    Beschwerdemanagements stehen können (KEV-98).
--}}
{{-- id: Sprungziel nach dem Absenden (AnfrageRequest::mitSprungziel), sonst
     stünde man nach einem Fehler oben auf der Seite und sähe ihn nicht. --}}
<section id="formular-f" @class([
    'scroll-mt-24 px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
]) aria-labelledby="formular-titel">
    <div class="mx-auto max-w-2xl">

        <h2 id="formular-titel" class="mb-2 font-display text-2xl font-medium text-green">
            {{ $titel }}
        </h2>

        <x-ui.nachricht-formular :herkunft="$herkunft" :auf="$auf" />
    </div>
</section>
