@props(['fassungen' => [], 'variant' => 'kopf'])

@php
    use App\Models\Language;
    use Illuminate\Support\Str;

    $aktiv = Language::aktive();
    $jetzt = Language::aktuell();

    $istKopf = $variant === 'kopf';
    $suchId = 'sprachsuche-'.$variant;
@endphp

{{--
    Sprachumschalter — Weltkugel-Knopf mit aufklappbarer, durchsuchbarer Liste.

    Vorbild ist der Umschalter von jw.org: ein Knopf mit Weltkugel, der ein Panel
    mit allen Sprachen öffnet. Die alte Linkliste (》DE EN RU《) sah nicht nur
    schlichter aus, sie skaliert auch nicht — bei zwanzig Sprachen wäre die
    Kopfzeile gesprengt.

    Zwei Zusagen des Projekts bleiben eingelöst:

    - **Ohne JavaScript bedienbar.** Der Aufklapper ist ein natives <details>,
      dasselbe Muster wie Mobilmenü und Akkordeon. Zu- und Aufklappen, Tastatur
      und Screenreader-Ansage kommen vom Browser. Das Suchfeld ist reine
      Verbesserung: Es erscheint erst, wenn JavaScript läuft (siehe Skript unten).
      Ohne Skript sieht man die vollständige Liste — nur eben ungefiltert.
    - **Kyrillisch lädt nur, wo es gebraucht wird.** Die Eigenbezeichnungen
      (》Русский《) stehen im Panel, also innerhalb des <details>. Auf einer
      deutschen Seite trägt der Knopf selbst nur die aktuelle Sprache (》Deutsch《).
      Ein Test prüft, dass ausserhalb der Aufklapper keine kyrillischen Zeichen
      stehen — sonst zöge jede deutsche Seite die kyrillischen Schriftschnitte mit.

    Das nonce stammt aus App\Http\Middleware\SicherheitsHeader; ohne es blockiert
    die Content-Security-Policy das Skript.
--}}
@if ($aktiv->count() > 1)
    <nav aria-label="{{ __('rahmen.sprache.auswahl') }}"
         @class([
             'relative hidden sm:block' => $istKopf,
             'mt-2' => ! $istKopf,
         ])>

        @if ($istKopf)
            <details data-sprachwahl class="group">
                <summary class="flex cursor-pointer select-none items-center gap-1.5 rounded-full px-2.5 py-1.5
                                text-sm text-ink-soft marker:content-none [&::-webkit-details-marker]:hidden
                                hover:bg-green-mist hover:text-ink focus-visible:bg-green-mist">
                    <x-ui.icon name="globe" :size="18" class="shrink-0" />
                    <span class="max-w-[9rem] truncate">{{ $jetzt->label }}</span>
                    <x-ui.icon name="chevron-down" :size="14"
                               class="shrink-0 transition-transform group-open:rotate-180" />
                    <span class="sr-only">{{ __('rahmen.sprache.waehlen') }}</span>
                </summary>

                <div class="absolute right-0 top-full z-40 mt-2 w-72 rounded-card border border-line
                            bg-card p-2 shadow-lg">
        @else
            <p class="mb-1 px-1 text-xs font-medium uppercase tracking-wide text-ink-soft">
                {{ __('rahmen.sprache.auswahl') }}
            </p>
            <div data-sprachwahl>
        @endif

                {{-- Gemeinsamer Inhalt beider Varianten: Suche · Liste · Leermeldung. --}}

                {{-- hidden = ohne JavaScript unsichtbar. Das Skript blendet es ein. --}}
                <div hidden data-sprachsuche-wrap class="mb-2 px-1">
                    <label class="sr-only" for="{{ $suchId }}">{{ __('rahmen.sprache.suchen') }}</label>
                    <input id="{{ $suchId }}" type="search" data-sprachsuche autocomplete="off"
                           placeholder="{{ __('rahmen.sprache.suchen') }}"
                           class="w-full rounded-full border border-line bg-cream px-4 py-2 text-sm
                                  text-ink placeholder:text-ink-soft focus-visible:border-green" />
                </div>

                <ul data-sprachliste class="max-h-72 overflow-y-auto">
                    @foreach ($aktiv as $sprache)
                        @php
                            $istAktuell = $sprache->code === $jetzt->code;
                            $ziel = $fassungen[$sprache->code] ?? $sprache->pfad('/');
                            // Suchtext deckt Eigenbezeichnung, deutschen Namen und
                            // Kürzel ab: „Русский“, „Russisch“ und „ru“ finden alle.
                            $suchtext = Str::lower($sprache->label.' '.$sprache->label_deutsch.' '.$sprache->code);
                        @endphp
                        <li data-sprachitem data-suchtext="{{ $suchtext }}">
                            <a href="{{ $ziel }}"
                               hreflang="{{ $sprache->code }}"
                               lang="{{ $sprache->code }}"
                               @if ($istAktuell) aria-current="true" @endif
                               class="group flex min-h-11 items-center gap-2.5 rounded-lg px-3 no-underline text-ink
                                      hover:bg-green-mist aria-[current=true]:font-medium aria-[current=true]:text-green">
                                <span class="flex w-4 shrink-0 justify-center text-green" aria-hidden="true">
                                    @if ($istAktuell)
                                        <x-ui.icon name="check" :size="16" />
                                    @endif
                                </span>
                                {{-- Sprachkürzel als Badge, länderneutral statt Flagge.
                                     aria-hidden: der volle Name daneben trägt die
                                     Bedeutung, „RU“ vorgelesen ergäbe nichts. Der
                                     Zustand kommt vom Link (group), nicht vom Badge. --}}
                                <span aria-hidden="true"
                                      class="flex h-6 w-8 shrink-0 items-center justify-center rounded-md
                                             bg-green-mist text-[0.6875rem] font-semibold uppercase tracking-wide
                                             text-green-deep group-aria-[current=true]:bg-green
                                             group-aria-[current=true]:text-on-green">{{ $sprache->code }}</span>
                                <span class="flex-1">{{ $sprache->label }}</span>
                                <span class="sr-only">
                                    {{ $istAktuell
                                        ? __('rahmen.sprache.aktuell', ['sprache' => $sprache->bezeichnung()])
                                        : __('rahmen.sprache.wechseln_zu', ['sprache' => $sprache->bezeichnung()]) }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Nur die Suche kann sie zeigen; ohne Treffer bleibt das Panel sonst leer. --}}
                <p hidden data-keine-treffer class="px-3 py-3 text-sm text-ink-soft">
                    {{ __('rahmen.sprache.keine_treffer') }}
                </p>

        @if ($istKopf)
                </div>
            </details>
        @else
            </div>
        @endif
    </nav>

    @if ($istKopf)
        <script @isset($cspNonce) nonce="{{ $cspNonce }}" @endisset>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-sprachwahl]').forEach(function (box) {
                    var wrap = box.querySelector('[data-sprachsuche-wrap]');
                    var suche = box.querySelector('[data-sprachsuche]');
                    var items = box.querySelectorAll('[data-sprachitem]');
                    var leer = box.querySelector('[data-keine-treffer]');

                    // Suche gibt es nur mit JavaScript — ohne bliebe sie funktionslos.
                    if (wrap) { wrap.hidden = false; }

                    if (suche) {
                        suche.addEventListener('input', function () {
                            var q = suche.value.trim().toLowerCase();
                            var treffer = 0;
                            items.forEach(function (li) {
                                var passt = !q || (li.getAttribute('data-suchtext') || '').indexOf(q) !== -1;
                                li.hidden = !passt;
                                if (passt) { treffer++; }
                            });
                            if (leer) { leer.hidden = treffer !== 0; }
                        });
                    }

                    // Nur das Dropdown im Kopf ist ein <details>. Die Menü-Variante
                    // liegt schon im offenen Mobilmenü und braucht kein eigenes.
                    var det = box.tagName === 'DETAILS' ? box : null;
                    if (det) {
                        det.addEventListener('toggle', function () {
                            if (det.open && suche) {
                                suche.value = '';
                                suche.dispatchEvent(new Event('input'));
                                suche.focus();
                            }
                        });
                        document.addEventListener('keydown', function (e) {
                            if (e.key === 'Escape' && det.open) {
                                det.open = false;
                                var s = det.querySelector('summary');
                                if (s) { s.focus(); }
                            }
                        });
                        document.addEventListener('click', function (e) {
                            if (det.open && !det.contains(e.target)) { det.open = false; }
                        });
                    }
                });
            });
        </script>
    @endif
@endif
