<?php

namespace App\Mail;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Bestätigung an die Person, die sich zu einer Selbsthilfegruppe angemeldet
 * hat (KEV-226): angekommen, der Zugangslink folgt nach der Freigabe.
 *
 * Nicht ShouldQueue, aus demselben Grund wie NachrichtAnOmbudsstelle: In der
 * Warteschlange stünde die Adresse im Klartext in der Tabelle `jobs`.
 */
class AnmeldungEingegangen extends Mailable
{
    use Queueable;

    public function __construct(public readonly Group $gruppe, public readonly ?string $name) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Deine Anmeldung zu „:gruppe“', ['gruppe' => $this->gruppe->name]));
    }

    public function content(): Content
    {
        return new Content(text: 'mail.anmeldung-eingegangen');
    }
}
