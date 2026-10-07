<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Eine Beschwerde über den Verein, für die unabhängige Ombudsstelle (KEV-98).
 *
 * Anders als App\Notifications\NeueAnfrage MIT Inhalt: Die Ombudsstelle hat
 * keinen Zugang zum Verwaltungsbereich, und dort soll die Nachricht auch nicht
 * liegen, weil der Verein sie dann lesen könnte. Die E-Mail ist hier der
 * einzige Ort, an dem die Nachricht existiert.
 *
 * Bewusst nicht ShouldQueue: In der Warteschlange stünde der Inhalt in der
 * Tabelle `jobs`, im Klartext und für jeden mit Datenbankzugang lesbar.
 *
 * Nur Text, kein HTML und kein Markdown: Was jemand ins Formular schreibt,
 * soll bei der Ombudsstelle genau so ankommen und nicht als Link oder
 * Formatierung gedeutet werden.
 */
class NachrichtAnOmbudsstelle extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly string $betreff,
        public readonly string $nachricht,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Beschwerde über die Website: '.$this->betreff,
            // Antworten gehen direkt an die Person, nicht an den Absender der
            // Website, über dessen Postfach der Verein liest.
            replyTo: $this->email ? [new Address($this->email, $this->name ?? '')] : [],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.ombudsstelle');
    }
}
