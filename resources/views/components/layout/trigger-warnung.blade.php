@props(['seite' => null, 'ersatzsprache' => null])

@if ($seite)
{{--
    Vorgeschalteter Hinweis auf belastende Inhalte.

    ── Warum <dialog open> und nicht ein <div>, das JavaScript aufbaut ──────────

    Ohne JavaScript ist ein `<dialog open>` ein ganz normaler Block im
    Seitenfluss: der Hinweis steht sichtbar über allem, genau wie gewünscht.
    Mit JavaScript wird daraus per showModal() ein echter Dialog — mit
    Fokusfalle, Hintergrund-Abdunklung und ESC, alles vom Browser.

    Andersherum wäre es fahrlässig: Ein Overlay, das erst JavaScript erzeugt,
    gibt bei jedem Skriptfehler und in jedem Browser mit abgeschaltetem
    JavaScript den Inhalt ungewarnt frei. Bei dieser Zielgruppe ist das der
    eine Fehler, den man nicht machen darf.

    ── Warum die beiden Knöpfe „data-trigger-braucht-js“ tragen ────────────────

    Wegklicken und „nicht mehr anzeigen“ brauchen zwingend JavaScript — beides
    ist ein gespeicherter Zustand im Browser. Ohne JavaScript blieben es Knöpfe,
    die auf Druck nichts tun. Die CSS blendet sie deshalb aus, bis das Skript
    sie wirklich verdrahtet hat — nicht schon dann, wenn JavaScript bloss
    grundsätzlich eingeschaltet ist. Ein abgebrochenes Bundle ist sonst genau
    der Fall, der durchrutscht.

    Der Notausgang ist davon ausgenommen: Er ist ein echtes <a href> und
    funktioniert immer.
--}}
<dialog id="trigger-warnung"
        open
        aria-labelledby="trigger-warnung-titel"
        @if ($ersatzsprache) lang="{{ $ersatzsprache->code }}" dir="{{ $ersatzsprache->richtung }}" @endif>

    <div class="mx-auto w-full max-w-2xl rounded-card border border-line bg-card p-6 lg:p-8">

        <p class="mb-2 font-display text-xs font-semibold uppercase tracking-[0.14em] text-ink-soft">
            {{ __('rahmen.trigger.eyebrow') }}
        </p>

        {{-- Bewusst ein <p> und keine Überschrift.

             Der Hinweis steht im Quelltext vor dem Seiteninhalt. Ein <h2> hier
             wäre die erste Überschrift des Dokuments und führte die Gliederung
             an, bevor die h1 der Seite kommt — genau der Fehler, den die
             A11y-Toolbar an dieser Stelle schon einmal gemacht hat
             (`BarrierefreiheitTest::test_ueberschriften_bilden_eine_saubere_gliederung`).

             Verloren geht dabei nichts: Ein modaler Dialog wird beim Öffnen mit
             seinem Namen angekündigt, und den liefert aria-labelledby. --}}
        <p id="trigger-warnung-titel"
           class="mb-4 font-display text-2xl font-medium text-ink lg:text-3xl">
            {{ $seite->titel }}
        </p>

        {{-- Die Bausteine der Seite, ohne ihren üblichen Seitenabstand: im
             Dialog sitzen sie in einer Karte, nicht in einem Seitenraster.
             Die Klasse regelt das in app.css an einer Stelle — sonst müsste
             jeder Bausteintyp wissen, dass es diesen Ort gibt. --}}
        @php
            /*
             * Überschriften der Bausteine fliegen hier raus.
             *
             * Zwei Gründe, beide handfest: Der Titel der Seite steht oben schon,
             * eine zweite Überschrift wäre eine Dopplung. Und ein <h2> aus einem
             * Baustein stünde im Quelltext vor der h1 der Seite und brächte die
             * Gliederung durcheinander — der Verein soll das nicht wissen
             * müssen, wenn er den Text im Panel bearbeitet.
             *
             * replicate() statt direkter Zuweisung: Die Kopie ist nicht
             * gespeichert, am Datensatz ändert sich nichts.
             */
            $bausteine = $seite->blocks->map(function ($b) {
                $kopie = $b->replicate();
                $kopie->data = \Illuminate\Support\Arr::except($b->data ?? [], ['titel', 'eyebrow']);

                return $kopie;
            });
        @endphp

        <div class="trigger-warnung-inhalt">
            @foreach ($bausteine as $block)
                <x-block :block="$block" />
            @endforeach
        </div>

        {{-- Reihenfolge der Knöpfe ist eine Haltung: „weiterlesen“ steht vorn,
             weil es der Normalfall ist. Der Notausgang steht sichtbar abgesetzt
             am Ende und nicht dazwischen — er soll auffindbar sein, ohne wie
             die naheliegende Antwort auszusehen. --}}
        <div class="mt-6 flex flex-col gap-3 border-t border-line pt-6 sm:flex-row sm:flex-wrap sm:items-center">

            <x-ui.button type="button" data-trigger-weiter data-trigger-braucht-js>
                {{ __('rahmen.trigger.weiter') }}
            </x-ui.button>

            <x-ui.button type="button" variant="ghost" size="sm" data-trigger-nie data-trigger-braucht-js>
                {{ __('rahmen.trigger.nie_mehr') }}
            </x-ui.button>

            <a href="{{ config('navigation.exit_url') }}"
               data-notausgang
               rel="noreferrer noopener"
               class="inline-flex items-center justify-center gap-2 rounded-full border border-alert
                      px-4 py-2 text-sm text-alert no-underline hover:bg-alert hover:text-cream
                      sm:ms-auto">
                <x-ui.icon name="exit" :size="18" />
                {{ __('rahmen.trigger.verlassen') }}
            </a>
        </div>

        {{-- Steht nur da, solange JavaScript nicht übernommen hat. Ohne diesen
             Satz wirkt der Hinweis wie ein Kasten, den man nicht loswird —
             und niemand weiss, warum. --}}
        <p class="mt-4 text-sm text-ink-soft" data-trigger-ohne-js>
            {{ __('rahmen.trigger.ohne_js') }}
        </p>
    </div>
</dialog>
@endif
