<?php

namespace App\Http\Controllers\Auth;

use App\Enums\VerificationCodeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\VerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function __construct(
        private readonly VerificationCodeService $verificationCodeService,
    ) {}

    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->firstOrFail();

        $this->verificationCodeService->verifyOrFail(
            $user,
            $request->validated('code'),
            VerificationCodeType::PasswordReset,
        );

        $user->update([
            'password' => $request->validated('password'),
        ]);

        return redirect()
            ->route('login')
            ->with('status', 'Password updated. You can sign in with your new password.');
    }
}
