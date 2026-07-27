<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Hinweis an den Verein, dass eine Anfrage vorliegt.
 *
 * WICHTIG: Diese E-Mail enthält KEINEN Inhalt der Anfrage — weder Name noch
 * E-Mail-Adresse, Betreff oder Nachricht. Nur die Eingangszeit und einen Link
 * ins geschützte Panel.
 *
 * Grund: E-Mail wird unverschlüsselt übertragen, liegt bei Mailanbietern und
 * bleibt oft jahrelang in Postfächern liegen. Bei Angaben zu Straftaten und
 * Gesundheit wäre das das grösste Leck der ganzen Anwendung. Der Umweg über
 * das Panel kostet einen Klick und schliesst es.
 *
 * Beim Erweitern dieser Klasse bitte dabei bleiben.
 */
class NeueAnfrage extends Notification
{
    use Queueable;

    public function __construct(private readonly Inquiry $anfrage) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Neue Anfrage über die Website')
            ->greeting('Hallo,')
            ->line('über das Kontaktformular ist eine neue Anfrage eingegangen.')
            ->line('Eingegangen am '.$this->anfrage->created_at->format('d.m.Y').
                   ' um '.$this->anfrage->created_at->format('H:i').' Uhr.')
            ->action('Anfrage im Verwaltungsbereich öffnen',
                     url('/admin/inquiries/'.$this->anfrage->id.'/edit'))
            ->line('Der Inhalt der Anfrage steht bewusst nicht in dieser E-Mail: '
                  .'E-Mails werden unverschlüsselt übertragen. Die Nachricht liegt '
                  .'verschlüsselt in der Datenbank und ist nur nach Anmeldung einsehbar.')
            ->salutation('Viele Grüße');
    }
}
