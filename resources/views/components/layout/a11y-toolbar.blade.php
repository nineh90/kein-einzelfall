{{--
    Barrierefreiheits-Toolbar.

    Im Mockup ist das ein <div> mit title-Attribut — nicht fokussierbar, kein
    Accessible Name. Hier ein echter Button mit aria-expanded und aria-controls.
--}}
<div x-data class="relative">
    <button type="button"
            @click="$store.a11y.toggle()"
            :aria-expanded="$store.a11y.offen ? 'true' : 'false'"
            aria-expanded="false"
            aria-controls="a11y-panel"
            class="relative flex h-10 w-10 items-center justify-center rounded-full border
                   border-line bg-card text-green hover:bg-green-mist">
        <span class="sr-only">Darstellung und Barrierefreiheit einstellen</span>
        <x-ui.icon name="accessibility" :size="18" />

        {{-- Zähler zeigt, dass Einstellungen aktiv sind — sonst wundert man sich
             auf einem fremden Gerät über das veränderte Aussehen. --}}
        <span x-show="$store.a11y.anzahl > 0" x-cloak x-text="$store.a11y.anzahl"
              class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center
                     rounded-full bg-green px-1 text-[0.625rem] text-on-green"></span>
    </button>

    <div id="a11y-panel"
         x-show="$store.a11y.offen"
         x-cloak
         @click.outside="$store.a11y.offen = false"
         @keydown.escape.window="$store.a11y.offen = false"
         role="dialog"
         aria-labelledby="a11y-titel"
         class="fixed inset-x-2 top-20 z-50 max-h-[75vh] overflow-y-auto rounded-card border
                border-line bg-card p-4 shadow-lg
                sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 sm:w-80">

        <div class="mb-3 flex items-center justify-between gap-4">
            {{-- Bewusst kein <h2>: Die Toolbar steht im Quelltext vor der <h1> der
                 Seite. Als Überschrift ausgezeichnet würde sie die Dokument-Outline
                 anführen und Screenreader-Nutzer in die Irre schicken. Der Dialog
                 ist über aria-labelledby trotzdem sauber benannt. --}}
            <p id="a11y-titel" class="font-display text-lg text-ink">Darstellung</p>
            <button type="button" @click="$store.a11y.offen = false"
                    class="flex h-8 w-8 items-center justify-center rounded-full text-ink-soft hover:bg-green-mist">
                <span class="sr-only">Schließen</span>
                <x-ui.icon name="close" :size="18" />
            </button>
        </div>

        <template x-for="(opt, key) in $store.a11y.optionen" :key="key">
            <div class="border-b border-line py-3 last:border-0">

                {{-- Stufenregler: Schriftgröße, Zeilen-, Buchstabenabstand --}}
                <template x-if="opt.typ === 'stufen'">
                    <div>
                        <p class="mb-2 text-sm font-medium text-ink" x-text="opt.label"></p>
                        <div class="flex flex-wrap gap-1.5" role="group" :aria-label="opt.label">
                            <template x-for="stufe in opt.stufen" :key="stufe.wert">
                                <button type="button"
                                        @click="$store.a11y.setzen(key, stufe.wert)"
                                        :aria-pressed="($store.a11y.werte[key] || 0) === stufe.wert ? 'true' : 'false'"
                                        class="rounded-full border border-line px-3 py-1.5 text-xs
                                               aria-[pressed=true]:border-green aria-[pressed=true]:bg-green
                                               aria-[pressed=true]:text-on-green"
                                        x-text="stufe.label"></button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Auswahl: Kontrastmodi --}}
                <template x-if="opt.typ === 'auswahl'">
                    <div>
                        <p class="mb-2 text-sm font-medium text-ink" x-text="opt.label"></p>
                        <div class="flex flex-wrap gap-1.5" role="group" :aria-label="opt.label">
                            <template x-for="o in opt.optionen" :key="o.wert">
                                <button type="button"
                                        @click="$store.a11y.setzen(key, o.wert)"
                                        :aria-pressed="($store.a11y.werte[key] || '') === o.wert ? 'true' : 'false'"
                                        class="rounded-full border border-line px-3 py-1.5 text-xs
                                               aria-[pressed=true]:border-green aria-[pressed=true]:bg-green
                                               aria-[pressed=true]:text-on-green"
                                        x-text="o.label"></button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Einfache Schalter --}}
                <template x-if="opt.typ === 'schalter'">
                    <button type="button"
                            @click="$store.a11y.umschalten(key)"
                            :aria-pressed="$store.a11y.werte[key] ? 'true' : 'false'"
                            class="flex w-full items-center justify-between gap-3 text-left">
                        <span class="text-sm text-ink" x-text="opt.label"></span>
                        <span class="relative h-6 w-11 shrink-0 rounded-full bg-line transition-colors"
                              :class="$store.a11y.werte[key] && 'bg-green'">
                            <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-card transition-transform"
                                  :class="$store.a11y.werte[key] && 'translate-x-5'"></span>
                        </span>
                    </button>
                </template>
            </div>
        </template>

        <button type="button" @click="$store.a11y.zuruecksetzen()"
                class="mt-3 w-full rounded-full border border-line py-2 text-sm text-ink-soft hover:bg-green-mist">
            Alles zurücksetzen
        </button>

        <p class="mt-3 text-xs text-ink-soft">
            Die Einstellungen bleiben auf diesem Gerät gespeichert.
            <a href="/barrierefreiheit" class="text-green-deep underline">Mehr zur Barrierefreiheit</a>
        </p>
    </div>
</div>
