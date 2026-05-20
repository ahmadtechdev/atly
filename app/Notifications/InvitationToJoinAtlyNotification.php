<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when a person is invited to a Task/Project/Workspace but does not yet
 * have an ATLY account. We pitch ATLY in the email so they know what they
 * are being invited to, and provide a link to register. After registering
 * with the same email the invitation is auto-linked.
 */
class InvitationToJoinAtlyNotification extends Notification
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

        $registerUrl = route('register').'?email='.urlencode((string) $invitation->invitee_email);

        $mail = (new MailMessage)
            ->subject("{$inviterName} invited you to join {$appName}")
            ->greeting('Hi there!')
            ->line("{$inviterName} wants to collaborate with you on the {$kind} \"{$label}\" in {$appName}.")
            ->line("**What is {$appName}?**")
            ->line("{$appName} is a calm, focused workspace where teams plan workspaces, projects, and tasks, track time, and discuss progress together — without the noise of typical project tools.")
            ->line("To accept this invitation, create your free account using **this email address** ({$invitation->invitee_email}). The invitation will be waiting in your Invitations page after you sign in.");

        if ($invitation->message !== null && $invitation->message !== '') {
            $mail->line('Message from '.$inviterName.':')
                ->line('"'.$invitation->message.'"');
        }

        return $mail
            ->action("Create your {$appName} account", $registerUrl)
            ->line('This invitation expires in 14 days. See you soon!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'invitee_email' => $this->invitation->invitee_email,
            'inviter_id' => $this->invitation->inviter_id,
        ];
    }
}
