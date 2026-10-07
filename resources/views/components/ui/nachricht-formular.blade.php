@props([
    // Wege zur Auswahl, list<array{wert: string, titel: string}>. Leer: ein
    // gewöhnliches Kontaktformular, das im Verwaltungsbereich landet
    // (AnfrageController). Mit Wegen (Beschwerdemanagement, KEV-98) wählt
    // man oben, wohin die Nachricht geht (BeschwerdeController):
    //   anfrage      → verschlüsselt in den Verwaltungsbereich
    //   ombudsstelle → per E-Mail an die unabhängige Ombudsstelle, nirgends
    //                  gespeichert
    'wege' => [],
    // Vorsilbe der Feld-IDs. Stehen zwei Formulare auf einer Seite, braucht
    // jedes eine eigene, sonst zeigten zwei Beschriftungen auf dasselbe Feld.
    'kennung' => 'f',
    'herkunft' => null,
    'auf' => 'cream',    // Fläche, auf der das Formular steht: cream | card
])

@php
    // Felder und Kästen stehen auf der jeweils anderen Fläche, sonst
    // verschwämmen sie auf der Karte mit dem Hintergrund.
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';

    $auswahl = filled($wege);

    // Vorauswahl über die Adresse (?weg=ombudsstelle): Die Knöpfe unter den
    // Texten der Seite setzen sie, ganz ohne JavaScript. Eine frühere Eingabe
    // geht vor. Ohne beides ist nichts gewählt, mit Absicht (BeschwerdeRequest).
    $gewaehlt = old('weg', request()->query('weg'));

    // Stehen einmal zwei Formulare auf einer Seite (etwa ein Kontaktformular
    // unter dem Beschwerdeformular): Fehler, alte Eingaben und die
    // Bestätigung gehören nur zu dem, das abgeschickt wurde.
    // Ohne Angabe (ältere Sitzungen, Tests) gilt alles für jedes Formular,
    // wie bisher mit einem einzigen.
    $meins = fn ($wer) => $wer === null || $wer === $kennung;
    $aktiv = $meins(old('formular'));
    $fehler = $aktiv ? $errors->getBag('default') : new \Illuminate\Support\MessageBag;
    $alt = fn (string $feld) => $aktiv ? old($feld) : null;

    $id = fn (string $feld) => $kennung.'-'.$feld;
    $feldKlassen = fn (string $feld) => ['w-full rounded-lg border px-4 py-3 text-ink', $innen,
        'border-line' => ! $fehler->has($feld),
        'border-alert' => $fehler->has($feld)];
@endphp

{{--
    Die Felder des Kontaktformulars, ohne Fläche und Überschrift.

    Bewusst ein normales HTML-Formular ohne JavaScript: Es muss auch dann
    absendbar sein, wenn Skripte blockiert sind — etwa in gehärteten Browsern
    oder über Tor, was bei dieser Zielgruppe vorkommt.

    Barrierefreiheit:
      - jedes Feld hat ein echtes <label> (kein Platzhalter als Ersatz)
      - Fehler stehen direkt am Feld und sind über aria-describedby verknüpft
      - eingegebene Werte bleiben nach einem Fehler erhalten (old())
      - Pflichtfelder sind im Text benannt, nicht nur durch ein Sternchen
--}}
<div {{ $attributes }}>
    <p class="mb-6 text-ink-soft">
        Du entscheidest, was du schreibst. Pflicht sind nur Betreff und Nachricht —
        deinen Namen und deine E-Mail-Adresse kannst du weglassen.
    </p>

    @if (session('anfrage_versendet') && $meins(session('versendet_von')))
        {{-- role="status" statt "alert": die Meldung ist eine Bestätigung,
             keine Warnung. Screenreader lesen sie beim Laden vor. --}}
        <div role="status"
             class="mb-6 rounded-card border-2 border-green bg-green-mist px-5 py-4">
            <p class="font-medium text-green-deep">Deine Nachricht ist angekommen.</p>
            <p class="mt-1 text-sm text-ink">
                {{ session('anfrage_versendet') }}
            </p>
        </div>
    @endif

    @if (session('versand_fehlgeschlagen') === $kennung)
        {{-- Die Nachricht ist nirgends gespeichert, also muss der Weg
             daneben genau hier stehen. Der Text bleibt in den Feldern. --}}
        <div role="alert"
             class="mb-6 rounded-card border-2 border-alert {{ $innen }} px-5 py-4">
            <p class="font-medium text-alert">Deine Nachricht konnte gerade nicht verschickt werden.</p>
            <p class="mt-1 text-sm text-ink">
                Dein Text steht noch unten im Formular. Du kannst ihn kopieren und direkt an
                {{ hervorheben(config('mail.ombudsstelle_an')) }} schicken.
            </p>
        </div>
    @endif

    @if ($fehler->any())
        <div role="alert"
             class="mb-6 rounded-card border-2 border-alert {{ $innen }} px-5 py-4">
            <p class="font-medium text-alert">Bitte prüfe noch einmal:</p>
            <ul class="mt-2 list-disc pl-5 text-sm text-ink">
                @foreach ($fehler->all() as $meldung)
                    <li>{{ $meldung }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ sprachlink($auswahl ? 'beschwerde.senden' : 'anfrage.senden') }}"
          class="flex flex-col gap-5">
        @csrf
        <input type="hidden" name="herkunft" value="{{ $herkunft ?? request()->path() }}">
        <input type="hidden" name="formular" value="{{ $kennung }}">

        {{-- Honigtopf: für Menschen unsichtbar und aus dem Tab-Verlauf
             genommen, für automatische Ausfüller aber verlockend.
             Kein CAPTCHA — das wäre eine zusätzliche Hürde ausgerechnet für
             die Menschen, die ohnehin Mühe haben. --}}
        <div aria-hidden="true" class="absolute left-[-9999px] h-0 overflow-hidden">
            <label for="{{ $id('webseite') }}">Dieses Feld bitte frei lassen</label>
            <input type="text" name="webseite" id="{{ $id('webseite') }}" tabindex="-1" autocomplete="off">
        </div>
        <input type="hidden" name="gestartet_um" value="{{ encrypt(now()->timestamp) }}">

        @if ($auswahl)
            {{-- Die Wahl steht zuerst, und bei jedem Weg direkt, wohin die
                 Nachricht geht und was mit ihr passiert. Wer sich über den
                 Verein beschwert, soll das wissen, bevor er schreibt. --}}
            <fieldset @if ($fehler->has('weg')) aria-describedby="{{ $id('weg-fehler') }}" @endif>
                <legend class="mb-2 font-medium text-ink">
                    Worum geht es? <span class="font-normal text-ink-soft">(bitte auswählen)</span>
                </legend>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($wege as $weg)
                        <label for="{{ $id('weg-'.$weg['wert']) }}"
                               @class(['flex cursor-pointer items-start gap-3 rounded-card border px-4 py-3', $innen,
                                       'has-[:checked]:border-green has-[:checked]:bg-green-mist',
                                       'border-line' => ! $fehler->has('weg'),
                                       'border-alert' => $fehler->has('weg')])>
                            <input type="radio" name="weg" value="{{ $weg['wert'] }}" id="{{ $id('weg-'.$weg['wert']) }}"
                                   required class="mt-1 h-5 w-5 shrink-0 accent-[#00702F]"
                                   @checked($gewaehlt === $weg['wert'])
                                   @if ($fehler->has('weg')) aria-invalid="true" @endif>
                            <span>
                                <span class="block font-medium text-ink">{{ $weg['titel'] }}</span>
                                <span class="mt-0.5 block text-sm text-ink-soft">
                                    @if ($weg['wert'] === 'ombudsstelle')
                                        Geht direkt an die unabhängige Ombudsstelle. Der Verein
                                        sieht sie nicht, die Website speichert sie nicht.
                                    @else
                                        Geht an unser Team und wird verschlüsselt gespeichert.
                                    @endif
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @if ($fehler->has('weg'))
                    <p id="{{ $id('weg-fehler') }}" class="mt-1.5 text-sm text-alert">{{ $fehler->first('weg') }}</p>
                @endif
            </fieldset>
        @endif

        <div>
            <label for="{{ $id('name') }}" class="mb-1.5 block font-medium text-ink">
                Dein Name <span class="font-normal text-ink-soft">(freiwillig)</span>
            </label>
            <input type="text" name="name" id="{{ $id('name') }}" value="{{ $alt('name') }}"
                   autocomplete="name" maxlength="120"
                   @class($feldKlassen('name'))
                   @if ($fehler->has('name')) aria-describedby="{{ $id('name-fehler') }}" aria-invalid="true" @endif>
            @if ($fehler->has('name'))
                <p id="{{ $id('name-fehler') }}" class="mt-1.5 text-sm text-alert">{{ $fehler->first('name') }}</p>
            @endif
        </div>

        <div>
            <label for="{{ $id('email') }}" class="mb-1.5 block font-medium text-ink">
                Deine E-Mail-Adresse <span class="font-normal text-ink-soft">(freiwillig)</span>
            </label>
            <input type="email" name="email" id="{{ $id('email') }}" value="{{ $alt('email') }}"
                   autocomplete="email" maxlength="180"
                   aria-describedby="{{ $id('email-hinweis') }}{{ $fehler->has('email') ? ' '.$id('email-fehler') : '' }}"
                   @class($feldKlassen('email'))
                   @if ($fehler->has('email')) aria-invalid="true" @endif>
            <p id="{{ $id('email-hinweis') }}" class="mt-1.5 text-sm text-ink-soft">
                @if ($auswahl)
                    Ohne E-Mail-Adresse kann dir niemand antworten — deine Nachricht
                    kommt aber trotzdem an.
                @else
                    Ohne E-Mail-Adresse können wir dir nicht antworten — deine Nachricht
                    erreicht uns aber trotzdem.
                @endif
            </p>
            @if ($fehler->has('email'))
                <p id="{{ $id('email-fehler') }}" class="mt-1 text-sm text-alert">{{ $fehler->first('email') }}</p>
            @endif
        </div>

        <div>
            <label for="{{ $id('betreff') }}" class="mb-1.5 block font-medium text-ink">
                Betreff <span class="font-normal text-ink-soft">(muss ausgefüllt werden)</span>
            </label>
            <input type="text" name="betreff" id="{{ $id('betreff') }}" value="{{ $alt('betreff') }}"
                   required maxlength="200"
                   @class($feldKlassen('betreff'))
                   @if ($fehler->has('betreff')) aria-describedby="{{ $id('betreff-fehler') }}" aria-invalid="true" @endif>
            @if ($fehler->has('betreff'))
                <p id="{{ $id('betreff-fehler') }}" class="mt-1.5 text-sm text-alert">{{ $fehler->first('betreff') }}</p>
            @endif
        </div>

        <div>
            <label for="{{ $id('nachricht') }}" class="mb-1.5 block font-medium text-ink">
                Deine Nachricht <span class="font-normal text-ink-soft">(muss ausgefüllt werden)</span>
            </label>
            <textarea name="nachricht" id="{{ $id('nachricht') }}" rows="8" required maxlength="8000"
                      @class($feldKlassen('nachricht'))
                      @if ($fehler->has('nachricht')) aria-describedby="{{ $id('nachricht-fehler') }}" aria-invalid="true" @endif
            >{{ $alt('nachricht') }}</textarea>
            @if ($fehler->has('nachricht'))
                <p id="{{ $id('nachricht-fehler') }}" class="mt-1.5 text-sm text-alert">{{ $fehler->first('nachricht') }}</p>
            @endif
        </div>

        <div class="rounded-card border border-line {{ $innen }} px-5 py-4">
            <label for="{{ $id('einwilligung') }}" class="flex items-start gap-3">
                <input type="checkbox" name="einwilligung" id="{{ $id('einwilligung') }}" value="1"
                       required class="mt-1 h-5 w-5 shrink-0 rounded border-line accent-[#00702F]"
                       @if ($fehler->has('einwilligung')) aria-invalid="true" @endif>
                <span class="text-sm text-ink">
                    @if ($auswahl)
                        {{-- Beide Wege in einem Satz: Ohne JavaScript weiss die
                             Seite nicht, welcher gerade gewählt ist. --}}
                        Ich bin damit einverstanden, dass meine Angaben an die oben gewählte
                        Stelle gehen. Kritik wird bei uns verschlüsselt gespeichert und nach
                        der Bearbeitung gelöscht. Eine Beschwerde über den Verein geht per
                        E-Mail an die Ombudsstelle und wird auf der Website nicht gespeichert.
                        Mehr dazu in der
                    @else
                        Ich bin damit einverstanden, dass meine Angaben zur Bearbeitung
                        meiner Anfrage gespeichert werden. Die Übertragung ist verschlüsselt,
                        die Nachricht wird verschlüsselt gespeichert und nach der Bearbeitung
                        gelöscht. Mehr dazu in der
                    @endif
                    <a href="/datenschutz" class="text-green-deep underline">Datenschutzerklärung</a>.
                </span>
            </label>
            @if ($fehler->has('einwilligung'))
                <p class="mt-2 text-sm text-alert">{{ $fehler->first('einwilligung') }}</p>
            @endif
        </div>

        <div>
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-green
                           px-6 py-3 font-medium text-on-green transition-colors hover:bg-green-deep">
                Nachricht senden
            </button>
        </div>

        <ul class="flex flex-wrap gap-x-6 gap-y-2 text-xs text-ink-soft">
            @foreach ($auswahl
                ? [
                    ['icon' => 'lock',   'text' => 'Verschlüsselt übertragen'],
                    ['icon' => 'shield', 'text' => 'Beschwerden sieht nur die Ombudsstelle'],
                    ['icon' => 'users',  'text' => 'Auch ohne Namen möglich'],
                ]
                : [
                    ['icon' => 'lock',   'text' => 'Verschlüsselt übertragen'],
                    ['icon' => 'shield', 'text' => 'Verschlüsselt gespeichert'],
                    ['icon' => 'users',  'text' => 'Auch ohne Namen möglich'],
                ] as $trust)
                <li class="flex items-center gap-1.5">
                    <x-ui.icon :name="$trust['icon']" :size="15" />
                    {{ $trust['text'] }}
                </li>
            @endforeach
        </ul>
    </form>
</div>
