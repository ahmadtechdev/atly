<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\AdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return $this->loginFailed($request, 'These credentials do not match our records.');
        }

        $user = Auth::user();

        if ($user === null || ! $user->isSuperAdmin()) {
            Auth::logout();

            return $this->loginFailed($request, 'You do not have access to the super admin panel.');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    private function loginFailed(AdminLoginRequest $request, string $message): RedirectResponse
    {
        return redirect()
            ->route('admin.login')
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => $message]);
    }
}
