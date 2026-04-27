<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GoogleAuthController extends Controller
{
    public function __construct(
        protected GoogleOAuthService $googleOAuthService
    ) {}

    public function redirect(): RedirectResponse
    {
        return $this->googleOAuthService->redirectToGoogle();
    }

    public function callback(): RedirectResponse
    {
        try {
            $user = $this->googleOAuthService->handleCallback();
            Auth::login($user, true);

            return $user->isAdmin()
                ? redirect()->intended(route('admin.dashboard'))
                : redirect()->intended(route('student.dashboard'));
        } catch (AccessDeniedHttpException $e) {
            return redirect()
                ->route('login')
                ->with('error', $e->getMessage());
        }
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
