@props(['page'])

{{--
    Sichtbarer Vermerk, dass diese Seite von uns vorbereitet und vom Verein
    noch nicht gegengelesen wurde.

    Vertraglich stellt der Verein die Inhalte. Die Seiten aus Abschnitt 6.2 des
    Strukturpapiers sind die Ausnahme: Sie beschreiben geltendes Recht, das
    sich aus amtlichen Quellen zusammentragen lässt, ohne dem Verein etwas in
    den Mund zu legen. Wer sie liest, muss das trotzdem wissen — und zwar
    bevor er anfängt zu lesen, nicht in einer Fussnote.

    Das ist hier keine Formalie: Wer diese Seiten liest, steckt in der Regel in
    einem laufenden Verfahren. Eine falsche Frist kostet dort keinen Komfort,
    sondern einen Anspruch.

    Dieselbe Machart wie der Sprachrückfall darüber: ruhig, keine Warnfarbe.
    Der Inhalt ist ja brauchbar, er ist nur noch nicht freigegeben. Ein
    Alarmton vertriebe genau die Menschen, die die Information brauchen.

    role="status" statt "alert": Vorlesehilfen sollen den Vermerk ankündigen,
    aber niemanden aus dem Lesefluss reissen.
--}}
@if ($page->ungeprueft ?? false)
    <div class="border-b border-line bg-cream px-4 md:px-8 py-3 lg:px-10">
        <p role="status"
           class="mx-auto flex max-w-6xl items-start gap-2.5 text-sm text-ink">
            <x-ui.icon name="info" :size="18" class="mt-0.5 shrink-0 text-green-deep" />
            <span>
                <strong class="font-medium">{{ __('rahmen.entwurf.titel') }}</strong>
                {{ __('rahmen.entwurf.text') }}
            </span>
        </p>
    </div>
@endif
