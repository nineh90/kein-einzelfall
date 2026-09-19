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

    ── Warum Knopf und Kästchen „data-trigger-braucht-js“ tragen ───────────────

    Wegklicken und „nicht mehr anzeigen“ brauchen zwingend JavaScript — beides
    ist ein gespeicherter Zustand im Browser. Ohne JavaScript wären es ein Knopf
    und ein Kästchen, die auf Druck nichts tun. Die CSS blendet sie deshalb aus,
    bis das Skript sie wirklich verdrahtet hat — nicht schon dann, wenn
    JavaScript bloss grundsätzlich eingeschaltet ist. Ein abgebrochenes Bundle
    ist sonst genau der Fall, der durchrutscht.

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

        <div class="mt-8 border-t border-line pt-6">

            {{-- „Nicht mehr anzeigen“ ist ein Kontrollkästchen und kein Knopf.

                 Der Verein hat es selbst so beschrieben: „…oder aber auch
                 auswählen kann ‚diese Meldung nicht mehr anzeigen‘“. Auswählen,
                 nicht drücken — und das trifft die Sache: Es ist eine
                 Einstellung, keine Handlung. Als dritter Knopf neben zwei
                 Handlungen stand es gleichrangig da und zwang zu einer
                 Entscheidung, die niemand treffen wollte.

                 Als Kontrollkästchen bleiben unten genau zwei Wege: weiterlesen
                 oder gehen. --}}
            <label class="mb-6 flex cursor-pointer items-center gap-3 text-[0.9375rem] text-ink-soft"
                   data-trigger-braucht-js>
                {{-- Natives <input>: Tastaturbedienung, Vorlesehilfe und der
                     Zustand „ausgewählt“ kommen vom Browser. Ein nachgebautes
                     Kästchen aus <div>s ist genau die Sorte Eigenbau, die auf
                     dieser Seite nichts zu suchen hat. --}}
                <input type="checkbox"
                       data-trigger-nie
                       class="h-5 w-5 shrink-0 rounded border-line accent-green">
                <span>{{ __('rahmen.trigger.nie_mehr') }}</span>
            </label>

            {{-- Zwei Wege, gleiche Größe, gleiche Höhe — beide über dieselbe
                 Knopf-Komponente, damit sie nicht wieder auseinanderlaufen.
                 „Weiterlesen“ steht vorn, weil es der Normalfall ist; der
                 Notausgang daneben, sichtbar unterschieden durch die Warnfarbe,
                 ohne wie die naheliegende Antwort auszusehen.

                 Auf schmalen Geräten untereinander und über die volle Breite:
                 Wer in einer angespannten Lage tippt, trifft eine ganze Zeile
                 zuverlässiger als eine halbe. --}}
            <div class="flex flex-col gap-3 sm:flex-row">

                <x-ui.button type="button"
                             class="w-full sm:w-auto"
                             data-trigger-weiter
                             data-trigger-braucht-js>
                    {{ __('rahmen.trigger.weiter') }}
                </x-ui.button>

                <x-ui.button :href="config('navigation.exit_url')"
                             variant="alert"
                             class="w-full sm:w-auto"
                             rel="noreferrer noopener"
                             data-notausgang>
                    <x-ui.icon name="exit" :size="18" />
                    {{ __('rahmen.trigger.verlassen') }}
                </x-ui.button>
            </div>

            {{-- Steht nur da, solange JavaScript nicht übernommen hat. Ohne
                 diesen Satz wirkt der Hinweis wie ein Kasten, den man nicht
                 loswird — und niemand weiss, warum. --}}
            <p class="mt-5 text-sm text-ink-soft" data-trigger-ohne-js>
                {{ __('rahmen.trigger.ohne_js') }}
            </p>
        </div>
    </div>
</dialog>
@endif
