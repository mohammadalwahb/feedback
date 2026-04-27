<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GoogleOAuthService
{
    public const STUDENT_DOMAIN = 'stud.uoz.edu.krd';

    public const ADMIN_DOMAIN = 'uoz.edu.krd';

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleCallback(): User
    {
        /** @var SocialiteUser $googleUser */
        $googleUser = Socialite::driver('google')->user();
        $email = strtolower((string) $googleUser->getEmail());

        if ($email === '' || ! str_contains($email, '@')) {
            throw new AccessDeniedHttpException(__('auth.invalid_email'));
        }

        $domain = substr($email, strpos($email, '@') + 1);

        return DB::transaction(function () use ($googleUser, $email, $domain) {
            if ($domain === self::STUDENT_DOMAIN) {
                $student = Student::query()->where('email', $email)->first();
                if (! $student) {
                    throw new AccessDeniedHttpException(__('auth.student_not_whitelisted'));
                }

                $user = User::query()->firstOrNew(['email' => $email]);
                $user->name = $googleUser->getName() ?? $student->english_name;
                $user->google_id = $googleUser->getId();
                $user->role = UserRole::Student;
                $user->password = $user->password ?? null;
                $user->save();

                if ($student->user_id !== $user->id) {
                    $student->forceFill(['user_id' => $user->id])->save();
                }

                return $user->fresh();
            }

            if ($domain === self::ADMIN_DOMAIN) {
                $admin = Admin::query()->where('email', $email)->first();
                if (! $admin) {
                    throw new AccessDeniedHttpException(__('auth.admin_not_whitelisted'));
                }

                $user = User::query()->firstOrNew(['email' => $email]);
                $user->name = $googleUser->getName() ?? $admin->english_name;
                $user->google_id = $googleUser->getId();
                $user->role = UserRole::Admin;
                $user->password = $user->password ?? null;
                $user->save();

                if ($admin->user_id !== $user->id) {
                    $admin->forceFill(['user_id' => $user->id])->save();
                }

                return $user->fresh();
            }

            throw new AccessDeniedHttpException(__('auth.domain_not_allowed'));
        });
    }
}
