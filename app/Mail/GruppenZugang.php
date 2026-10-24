<?php

namespace App\Mail;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Der Zugangslink zu einer Selbsthilfegruppe (KEV-226), nach der Freigabe im
 * Verwaltungsbereich oder, wenn die Gruppe es so eingestellt hat, sofort.
 *
 * Mit Termin, den beiden Dokumenten und dem Weg, sich wieder abzumelden.
 * Antworten gehen an das Postfach der Selbsthilfegruppen. Nicht
 * ShouldQueue: Der Link stünde sonst im Klartext in der Tabelle `jobs`.
 */
class GruppenZugang extends Mailable
{
    use Queueable;

    public function __construct(public readonly Group $gruppe, public readonly ?string $name) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Dein Zugang zu „:gruppe“', ['gruppe' => $this->gruppe->name]),
            replyTo: [new Address(Group::SHG_ADRESSE)],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.gruppen-zugang');
    }
}
