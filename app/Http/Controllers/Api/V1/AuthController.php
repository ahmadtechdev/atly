<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\VerificationCodeType;
use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\VerificationCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private readonly VerificationCodeService $verificationCodeService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create($request->validated());

        $this->verificationCodeService->send($user, VerificationCodeType::EmailVerification);

        return $this->jsonSuccess(
            'Registration successful. Check your email for a 6-digit verification code.',
            ['user' => new UserResource($user)],
            201,
        );
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return $this->jsonError('Email is already verified.', 422);
        }

        $this->verificationCodeService->verifyOrFail(
            $user,
            $request->validated('code'),
            VerificationCodeType::EmailVerification,
        );

        $user->markEmailAsVerified();

        return $this->tokenResponse($user, 'Email verified successfully.');
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email', 'exists:users,email']]);

        $user = User::query()->where('email', $request->string('email'))->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return $this->jsonError('Email is already verified.', 422);
        }

        $this->verificationCodeService->send($user, VerificationCodeType::EmailVerification);

        return $this->jsonSuccess('A new verification code has been sent.');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return $this->jsonError('Invalid credentials.', 401);
        }

        /** @var User $user */
        $user = auth('api')->user();

        if (! $user->hasVerifiedEmail()) {
            auth('api')->logout();

            return $this->jsonError('Please verify your email before signing in.', 403, [
                'email' => [$user->email],
            ]);
        }

        return $this->tokenResponse($user, 'Login successful.', $token);
    }

    public function me(): JsonResponse
    {
        return $this->jsonSuccess('Authenticated user.', [
            'user' => new UserResource(auth('api')->user()),
        ]);
    }

    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return $this->jsonSuccess('Logged out successfully.');
    }

    public function refresh(): JsonResponse
    {
        $token = auth('api')->refresh();

        /** @var User $user */
        $user = auth('api')->user();

        return $this->tokenResponse($user, 'Token refreshed.', $token);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->firstOrFail();

        $this->verificationCodeService->send($user, VerificationCodeType::PasswordReset);

        return $this->jsonSuccess('Password reset code sent to your email.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->firstOrFail();

        $this->verificationCodeService->verifyOrFail(
            $user,
            $request->validated('code'),
            VerificationCodeType::PasswordReset,
        );

        $user->update(['password' => $request->validated('password')]);

        return $this->jsonSuccess('Password has been reset successfully.');
    }

    private function tokenResponse(User $user, string $message, ?string $token = null): JsonResponse
    {
        $token ??= JWTAuth::fromUser($user);

        return $this->jsonSuccess($message, [
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
