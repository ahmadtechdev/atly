<?php

namespace App\Services;

use App\Enums\VerificationCodeType;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class VerificationCodeService
{
    public function __construct(
        private readonly int $codeLength = 6,
        private readonly int $expiryMinutes = 15,
    ) {}

    public function send(User $user, VerificationCodeType $type): void
    {
        $this->ensureNotRateLimited($user, $type);

        $plainCode = $this->generatePlainCode();

        VerificationCode::query()
            ->where('user_id', $user->id)
            ->where('type', $type->value)
            ->delete();

        VerificationCode::query()->create([
            'user_id' => $user->id,
            'type' => $type->value,
            'code' => Hash::make($plainCode),
            'expires_at' => now()->addMinutes($this->expiryMinutes),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail(
            user: $user,
            plainCode: $plainCode,
            type: $type,
            expiresInMinutes: $this->expiryMinutes,
        ));

        RateLimiter::hit($this->rateLimitKey($user, $type), $this->expiryMinutes * 60);
    }

    public function verify(User $user, string $plainCode, VerificationCodeType $type): bool
    {
        $record = VerificationCode::query()
            ->where('user_id', $user->id)
            ->where('type', $type->value)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($record === null || ! Hash::check($plainCode, $record->code)) {
            return false;
        }

        $record->delete();

        VerificationCode::query()
            ->where('user_id', $user->id)
            ->where('type', $type->value)
            ->delete();

        return true;
    }

    public function verifyOrFail(User $user, string $plainCode, VerificationCodeType $type): void
    {
        if (! $this->verify($user, $plainCode, $type)) {
            throw ValidationException::withMessages([
                'code' => ['The verification code is invalid or has expired.'],
            ]);
        }
    }

    private function generatePlainCode(): string
    {
        return str_pad((string) random_int(0, 10 ** $this->codeLength - 1), $this->codeLength, '0', STR_PAD_LEFT);
    }

    private function ensureNotRateLimited(User $user, VerificationCodeType $type): void
    {
        $key = $this->rateLimitKey($user, $type);

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => ["Please wait {$seconds} seconds before requesting another code."],
            ]);
        }
    }

    private function rateLimitKey(User $user, VerificationCodeType $type): string
    {
        return "verification-code:{$type->value}:{$user->id}";
    }
}
