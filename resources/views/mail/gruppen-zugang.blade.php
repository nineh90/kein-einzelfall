{{-- Zugangslink zur Selbsthilfegruppe (KEV-226). Reiner Text: {!! !!} statt {{ }}. --}}
{!! $name ? __('Hallo :name,', ['name' => $name]) : __('Hallo,') !!}

{!! __('schön, dass du dabei bist! Deine Anmeldung zur Selbsthilfegruppe „:gruppe“ ist bestätigt.', ['gruppe' => $gruppe->name]) !!}

{!! __('Dein Zugang (Microsoft Teams):') !!}
{!! $gruppe->zugangslink !!}

{!! __('Bitte gib den Link nicht weiter, er ist nur für dich.') !!}

@include('mail._gruppe-termin', ['gruppe' => $gruppe])

{!! __('Mit der Teilnahme gelten die Teilnahme- und Verschwiegenheitsvereinbarung und die Gruppenregeln:') !!} {!! sprachlink('page', ['slug' => 'selbsthilfegruppen']) !!}#dl-dokumente-zum-herunterladen

{!! __('Du möchtest nicht mehr teilnehmen oder hast eine Frage? Schreib uns an :adresse.', ['adresse' => \App\Models\Group::SHG_ADRESSE]) !!}

{!! __('Herzliche Grüße') !!}
{!! __('Das Team von KE!N EINZELFALL e.V.') !!}
