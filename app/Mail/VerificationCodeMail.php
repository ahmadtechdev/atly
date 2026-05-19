<?php

namespace App\Mail;

use App\Enums\VerificationCodeType;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $plainCode,
        public VerificationCodeType $type,
        public int $expiresInMinutes,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->type) {
            VerificationCodeType::EmailVerification => 'Verify your '.config('atly.name').' account',
            VerificationCodeType::PasswordReset => 'Reset your '.config('atly.name').' password',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.verification-code',
        );
    }
}
