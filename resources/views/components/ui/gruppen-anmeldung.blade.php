@props([
    'gruppe',            // App\Models\Group, eine offene Selbsthilfegruppe
    'kennung' => 'anmeldung',
    'auf' => 'cream',    // Fläche, auf der das Formular steht: cream | card
])

{{--
    Anmeldung zu einer Selbsthilfegruppe (KEV-226). Ablauf in
    App\Support\GruppenAnmeldung.

    Gebaut wie das Kontaktformular (x-ui.nachricht-formular): normales
    HTML-Formular ohne JavaScript, echte Beschriftungen, Fehler am Feld,
    Eingaben bleiben nach einem Fehler stehen, Honigtopf und Zeitfalle statt
    CAPTCHA. Drei Pflicht-Häkchen: Vereinbarung, Gruppenregeln (Taddis
    Wunsch, beide verlinkt) und die ausdrückliche Einwilligung, weil schon
    die Anmeldung eine Gesundheitsangabe ist.
--}}
@php
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';
    $meins = fn ($wer) => $wer === null || $wer === $kennung;
    $aktiv = $meins(old('formular'));
    $fehler = $aktiv ? $errors->getBag('default') : new \Illuminate\Support\MessageBag;
    $alt = fn (string $feld) => $aktiv ? old($feld) : null;
    $id = fn (string $feld) => $kennung.'-'.$feld;
    $feldKlassen = fn (string $feld) => ['w-full rounded-lg border px-4 py-3 text-ink', $innen,
        'border-line' => ! $fehler->has($feld),
        'border-alert' => $fehler->has($feld)];
    $dokumente = \App\Support\GruppenAnmeldung::dokumente();
    $sofort = $gruppe->link_automatisch && filled($gruppe->zugangslink);
    $ergebnis = $meins(session('versendet_von')) ? session('anmeldung_ergebnis') : null;
@endphp

<div id="formular-{{ $kennung }}" {{ $attributes }}>
    <p class="mb-6 text-ink-soft">
        {{ $sofort
            ? __('Melde dich hier an. Den Zugangslink zur Gruppe schicken wir dir direkt danach per E-Mail.')
            : __('Melde dich hier an. Den Zugangslink zur Gruppe schicken wir dir per E-Mail, sobald wir deine Anmeldung bestätigt haben.') }}
    </p>

    @if ($ergebnis)
        <div role="status" class="mb-6 rounded-card border-2 border-green bg-green-mist px-5 py-4">
            <p class="font-medium text-green-deep">{{ __('Deine Anmeldung ist angekommen.') }}</p>
            <p class="mt-1 text-sm text-ink">
                @switch($ergebnis)
                    @case('link')
                        {{ __('Den Zugangslink haben wir dir gerade per E-Mail geschickt. Schau bitte auch im Spam-Ordner nach.') }}
                        @break
                    @case('bestaetigt')
                        {{ __('Wir haben dir eine Bestätigung per E-Mail geschickt. Den Zugangslink bekommst du, sobald wir deine Anmeldung bestätigt haben.') }}
                        @break
                    @default
                        {{ __('Wir melden uns per E-Mail bei dir, sobald wir sie bestätigt haben.') }}
                @endswitch
            </p>
        </div>
    @endif

    @if (session('formular_hinweis') && $meins(session('formular_hinweis_fuer')))
        <div role="alert" class="mb-6 rounded-card border-2 border-alert {{ $innen }} px-5 py-4">
            <p class="font-medium text-alert">{{ __('Deine Anmeldung ist noch nicht angekommen.') }}</p>
            <p class="mt-1 text-sm text-ink">{{ session('formular_hinweis') }}</p>
        </div>
    @endif

    @if ($fehler->any())
        <div role="alert" class="mb-6 rounded-card border-2 border-alert {{ $innen }} px-5 py-4">
            <p class="font-medium text-alert">{{ __('Bitte prüfe noch einmal:') }}</p>
            <ul class="mt-2 list-disc pl-5 text-sm text-ink">
                @foreach ($fehler->all() as $meldung)
                    <li>{{ $meldung }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ sprachlink('gruppen.anmelden', ['slug' => $gruppe->slug]) }}" class="flex flex-col gap-5">
        @csrf
        <input type="hidden" name="herkunft" value="{{ request()->path() }}">
        <input type="hidden" name="formular" value="{{ $kennung }}">

        <div aria-hidden="true" class="absolute left-[-9999px] h-0 overflow-hidden">
            <label for="{{ $id('webseite') }}">{{ __('Dieses Feld bitte frei lassen') }}</label>
            <input type="text" name="webseite" id="{{ $id('webseite') }}" tabindex="-1" autocomplete="off">
        </div>
        <input type="hidden" name="gestartet_um" value="{{ encrypt(now()->timestamp) }}">

        <div>
            <label for="{{ $id('name') }}" class="mb-1.5 block font-medium text-ink">
                {{ __('Dein Name') }} <span class="font-normal text-ink-soft">{{ __('(freiwillig)') }}</span>
            </label>
            <input type="text" name="name" id="{{ $id('name') }}" value="{{ $alt('name') }}"
                   autocomplete="given-name" maxlength="120"
                   aria-describedby="{{ $id('name-hinweis') }}"
                   @class($feldKlassen('name'))>
            <p id="{{ $id('name-hinweis') }}" class="mt-1.5 text-sm text-ink-soft">{{ __('Ein Vorname oder Spitzname reicht.') }}</p>
        </div>

        <div>
            <label for="{{ $id('email') }}" class="mb-1.5 block font-medium text-ink">
                {{ __('Deine E-Mail-Adresse') }} <span class="font-normal text-ink-soft">{{ __('(muss ausgefüllt werden)') }}</span>
            </label>
            <input type="email" name="email" id="{{ $id('email') }}" value="{{ $alt('email') }}"
                   required autocomplete="email" maxlength="180"
                   aria-describedby="{{ $id('email-hinweis') }}{{ $fehler->has('email') ? ' '.$id('email-fehler') : '' }}"
                   @class($feldKlassen('email'))
                   @if ($fehler->has('email')) aria-invalid="true" @endif>
            <p id="{{ $id('email-hinweis') }}" class="mt-1.5 text-sm text-ink-soft">{{ __('Dorthin schicken wir dir den Zugangslink.') }}</p>
            @if ($fehler->has('email'))
                <p id="{{ $id('email-fehler') }}" class="mt-1 text-sm text-alert">{{ $fehler->first('email') }}</p>
            @endif
        </div>

        <fieldset class="flex flex-col gap-3 rounded-card border border-line {{ $innen }} px-5 py-4">
            <legend class="sr-only">{{ __('Zustimmung') }}</legend>
            @foreach ([
                'teilnahme' => ['Ich habe die :dokument gelesen und stimme ihr zu.', __('Teilnahme- und Verschwiegenheitsvereinbarung'), $dokumente['teilnahme']],
                'gruppenregeln' => ['Ich habe die :dokument gelesen und halte mich daran.', __('Gruppenregeln'), $dokumente['gruppenregeln']],
            ] as $feld => [$satz, $dokument, $adresse])
                <div>
                    <label for="{{ $id($feld) }}" class="flex items-start gap-3">
                        <input type="checkbox" name="{{ $feld }}" id="{{ $id($feld) }}" value="1" required
                               class="mt-1 h-5 w-5 shrink-0 rounded border-line accent-[#00702F]"
                               @checked($alt($feld))
                               @if ($fehler->has($feld)) aria-invalid="true" @endif>
                        {{-- Der Link steht als Platzhalter im Satz: Im Englischen
                             sitzt er an anderer Stelle. Titel und Adresse maskiert,
                             der Satz selbst kommt aus unseren Übersetzungen. --}}
                        <span class="min-w-0 text-sm text-ink [overflow-wrap:anywhere]">
                            {!! __($satz, ['dokument' => '<a href="'.e($adresse).'" class="text-green-deep underline">'.e($dokument).'</a>']) !!}
                        </span>
                    </label>
                    @if ($fehler->has($feld))
                        <p class="mt-1 text-sm text-alert">{{ $fehler->first($feld) }}</p>
                    @endif
                </div>
            @endforeach

            <div>
                <label for="{{ $id('einwilligung') }}" class="flex items-start gap-3">
                    <input type="checkbox" name="einwilligung" id="{{ $id('einwilligung') }}" value="1" required
                           class="mt-1 h-5 w-5 shrink-0 rounded border-line accent-[#00702F]"
                           @checked($alt('einwilligung'))
                           @if ($fehler->has('einwilligung')) aria-invalid="true" @endif>
                    <span class="min-w-0 text-sm text-ink [overflow-wrap:anywhere]">
                        {{ __('Ich bin damit einverstanden, dass meine Anmeldung mit Name und E-Mail-Adresse gespeichert wird, um mir den Zugang zur Gruppe zu schicken. Mir ist klar, dass schon die Anmeldung zu einer Selbsthilfegruppe etwas über meine Gesundheit verraten kann. Die Angaben werden verschlüsselt gespeichert und nach kurzer Zeit gelöscht. Ich kann meine Einwilligung jederzeit widerrufen. Mehr dazu in der') }}
                        <a href="{{ \App\Models\Language::aktuell()->pfad('/datenschutz') }}" class="text-green-deep underline">{{ __('Datenschutzerklärung') }}</a>.
                    </span>
                </label>
                @if ($fehler->has('einwilligung'))
                    <p class="mt-1 text-sm text-alert">{{ $fehler->first('einwilligung') }}</p>
                @endif
            </div>
        </fieldset>

        <div>
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-green
                           px-6 py-3 font-medium text-on-green transition-colors hover:bg-green-deep">
                {{ __('Anmelden') }}
            </button>
        </div>
    </form>
</div>
