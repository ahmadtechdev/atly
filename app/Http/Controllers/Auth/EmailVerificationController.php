<?php

namespace App\Http\Controllers\Auth;

use App\Enums\VerificationCodeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Models\User;
use App\Services\VerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly VerificationCodeService $verificationCodeService,
    ) {}

    public function notice(Request $request): View
    {
        return view('auth.verify-email', [
            'email' => $request->string('email')->toString() ?: $request->session()->get('verification_email'),
        ]);
    }

    public function verify(VerifyEmailRequest $request): RedirectResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('status', 'Email already verified. You can sign in.');
        }

        $this->verificationCodeService->verifyOrFail(
            $user,
            $request->validated('code'),
            VerificationCodeType::EmailVerification,
        );

        $user->markEmailAsVerified();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Email verified successfully. Welcome to '.config('atly.name').'!');
    }

    public function resend(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email', 'exists:users,email']]);

        $user = User::query()->where('email', $request->string('email'))->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('status', 'Email already verified. You can sign in.');
        }

        $this->verificationCodeService->send($user, VerificationCodeType::EmailVerification);

        return back()->with('status', 'A new verification code has been sent to your email.');
    }
}
