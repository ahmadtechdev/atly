<?php

namespace App\Http\Controllers\Auth;

use App\Enums\VerificationCodeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Models\User;
use App\Services\VerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private readonly VerificationCodeService $verificationCodeService,
    ) {}

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->firstOrFail();

        $this->verificationCodeService->send($user, VerificationCodeType::PasswordReset);

        return redirect()
            ->route('password.reset', ['email' => $user->email])
            ->with('status', 'We sent a 6-digit reset code to your email.');
    }
}
