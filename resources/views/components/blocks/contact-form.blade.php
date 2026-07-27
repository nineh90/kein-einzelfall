@props([
    'titel' => 'Schreib uns',
    'herkunft' => null,
])

{{--
    Kontaktformular.

    Bewusst ein normales HTML-Formular ohne JavaScript: Es muss auch dann
    absendbar sein, wenn Skripte blockiert sind — etwa in gehärteten Browsern
    oder über Tor, was bei dieser Zielgruppe vorkommt.

    Barrierefreiheit:
      - jedes Feld hat ein echtes <label> (kein Platzhalter als Ersatz)
      - Fehler stehen direkt am Feld und sind über aria-describedby verknüpft
      - eingegebene Werte bleiben nach einem Fehler erhalten (old())
      - Pflichtfelder sind im Text benannt, nicht nur durch ein Sternchen
--}}
<section class="px-4 py-8 lg:px-10 lg:py-12" aria-labelledby="formular-titel">
    <div class="mx-auto max-w-2xl">

        <h2 id="formular-titel" class="mb-2 font-display text-2xl font-medium text-ink">
            {{ $titel }}
        </h2>

        <p class="mb-6 text-ink-soft">
            Du entscheidest, was du schreibst. Pflicht sind nur Betreff und Nachricht —
            deinen Namen und deine E-Mail-Adresse kannst du weglassen.
        </p>

        @if (session('anfrage_versendet'))
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

        @if ($errors->any())
            <div role="alert"
                 class="mb-6 rounded-card border-2 border-alert bg-card px-5 py-4">
                <p class="font-medium text-alert">Bitte prüfe noch einmal:</p>
                <ul class="mt-2 list-disc pl-5 text-sm text-ink">
                    @foreach ($errors->all() as $fehler)
                        <li>{{ $fehler }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ sprachlink('anfrage.senden') }}" class="flex flex-col gap-5">
            @csrf
            <input type="hidden" name="herkunft" value="{{ $herkunft ?? request()->path() }}">

            {{-- Honigtopf: für Menschen unsichtbar und aus dem Tab-Verlauf
                 genommen, für automatische Ausfüller aber verlockend.
                 Kein CAPTCHA — das wäre eine zusätzliche Hürde ausgerechnet für
                 die Menschen, die ohnehin Mühe haben. --}}
            <div aria-hidden="true" class="absolute left-[-9999px] h-0 overflow-hidden">
                <label for="webseite">Dieses Feld bitte frei lassen</label>
                <input type="text" name="webseite" id="webseite" tabindex="-1" autocomplete="off">
            </div>
            <input type="hidden" name="gestartet_um" value="{{ encrypt(now()->timestamp) }}">

            <div>
                <label for="f-name" class="mb-1.5 block font-medium text-ink">
                    Dein Name <span class="font-normal text-ink-soft">(freiwillig)</span>
                </label>
                <input type="text" name="name" id="f-name" value="{{ old('name') }}"
                       autocomplete="name" maxlength="120"
                       @class(['w-full rounded-lg border bg-card px-4 py-3 text-ink',
                               'border-line' => ! $errors->has('name'),
                               'border-alert' => $errors->has('name')])
                       @if ($errors->has('name')) aria-describedby="f-name-fehler" aria-invalid="true" @endif>
                @error('name')
                    <p id="f-name-fehler" class="mt-1.5 text-sm text-alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="f-email" class="mb-1.5 block font-medium text-ink">
                    Deine E-Mail-Adresse <span class="font-normal text-ink-soft">(freiwillig)</span>
                </label>
                <input type="email" name="email" id="f-email" value="{{ old('email') }}"
                       autocomplete="email" maxlength="180"
                       aria-describedby="f-email-hinweis{{ $errors->has('email') ? ' f-email-fehler' : '' }}"
                       @class(['w-full rounded-lg border bg-card px-4 py-3 text-ink',
                               'border-line' => ! $errors->has('email'),
                               'border-alert' => $errors->has('email')])
                       @if ($errors->has('email')) aria-invalid="true" @endif>
                <p id="f-email-hinweis" class="mt-1.5 text-sm text-ink-soft">
                    Ohne E-Mail-Adresse können wir dir nicht antworten — deine Nachricht
                    erreicht uns aber trotzdem.
                </p>
                @error('email')
                    <p id="f-email-fehler" class="mt-1 text-sm text-alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="f-betreff" class="mb-1.5 block font-medium text-ink">
                    Betreff <span class="font-normal text-ink-soft">(muss ausgefüllt werden)</span>
                </label>
                <input type="text" name="betreff" id="f-betreff" value="{{ old('betreff') }}"
                       required maxlength="200"
                       @class(['w-full rounded-lg border bg-card px-4 py-3 text-ink',
                               'border-line' => ! $errors->has('betreff'),
                               'border-alert' => $errors->has('betreff')])
                       @if ($errors->has('betreff')) aria-describedby="f-betreff-fehler" aria-invalid="true" @endif>
                @error('betreff')
                    <p id="f-betreff-fehler" class="mt-1.5 text-sm text-alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="f-nachricht" class="mb-1.5 block font-medium text-ink">
                    Deine Nachricht <span class="font-normal text-ink-soft">(muss ausgefüllt werden)</span>
                </label>
                <textarea name="nachricht" id="f-nachricht" rows="8" required maxlength="8000"
                          @class(['w-full rounded-lg border bg-card px-4 py-3 text-ink',
                                  'border-line' => ! $errors->has('nachricht'),
                                  'border-alert' => $errors->has('nachricht')])
                          @if ($errors->has('nachricht')) aria-describedby="f-nachricht-fehler" aria-invalid="true" @endif
                >{{ old('nachricht') }}</textarea>
                @error('nachricht')
                    <p id="f-nachricht-fehler" class="mt-1.5 text-sm text-alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-card border border-line bg-card px-5 py-4">
                <label for="f-einwilligung" class="flex items-start gap-3">
                    <input type="checkbox" name="einwilligung" id="f-einwilligung" value="1"
                           required class="mt-1 h-5 w-5 shrink-0 rounded border-line accent-[#2E4A3A]"
                           @if ($errors->has('einwilligung')) aria-invalid="true" @endif>
                    <span class="text-sm text-ink">
                        Ich bin damit einverstanden, dass meine Angaben zur Bearbeitung
                        meiner Anfrage gespeichert werden. Die Übertragung ist verschlüsselt,
                        die Nachricht wird verschlüsselt gespeichert und nach der Bearbeitung
                        gelöscht. Mehr dazu in der
                        <a href="/datenschutz" class="text-green-deep underline">Datenschutzerklärung</a>.
                    </span>
                </label>
                @error('einwilligung')
                    <p class="mt-2 text-sm text-alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-green
                               px-6 py-3 font-medium text-on-green transition-colors hover:bg-green-deep">
                    Nachricht senden
                </button>
            </div>

            <ul class="flex flex-wrap gap-x-6 gap-y-2 text-xs text-ink-soft">
                @foreach ([
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
</section>
