<x-mail::message>
# {{ $type === \App\Enums\VerificationCodeType::EmailVerification ? 'Verify your email' : 'Reset your password' }}

Hi {{ $user->name }},

Your verification code for **{{ config('atly.name') }}** is:

<x-mail::panel>
## {{ $plainCode }}
</x-mail::panel>

This code expires in **{{ $expiresInMinutes }} minutes**. Do not share it with anyone.

Thanks,<br>
{{ config('atly.name') }}
</x-mail::message>
