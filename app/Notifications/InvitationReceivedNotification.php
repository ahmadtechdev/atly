<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Invitation $invitation) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invitation = $this->invitation->loadMissing(['inviter', 'invitable']);
        $inviterName = $invitation->inviter?->name ?? 'Someone';
        $kind = $invitation->invitableKind();
        $label = $invitation->invitableLabel();
        $appName = config('app.name', 'ATLY');

        $mail = (new MailMessage)
            ->subject("{$inviterName} invited you to a {$kind} on {$appName}")
            ->greeting('Hello,')
            ->line("{$inviterName} invited you to collaborate on the {$kind} \"{$label}\".");

        if ($invitation->message !== null && $invitation->message !== '') {
            $mail->line('Message from '.$inviterName.':')
                ->line('"'.$invitation->message.'"');
        }

        return $mail
            ->action('Review invitation', route('invitations.index'))
            ->line('You can accept or decline this invitation from your Invitations page.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'invitable_type' => $this->invitation->invitable_type,
            'invitable_id' => $this->invitation->invitable_id,
            'inviter_id' => $this->invitation->inviter_id,
        ];
    }
}
