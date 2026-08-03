<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse as LaravelRedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse|LaravelRedirectResponse
    {
        if (! $this->googleIsConfigured()) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'google' => 'Login dengan Google belum dikonfigurasi. Hubungi pengelola SmartVolt.',
                ]);
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(): LaravelRedirectResponse
    {
        if (! $this->googleIsConfigured()) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'google' => 'Login dengan Google belum dikonfigurasi.',
                ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();

            $googleId = trim((string) $googleUser->getId());
            $email = Str::lower(trim((string) $googleUser->getEmail()));
            $name = trim((string) ($googleUser->getName() ?: $googleUser->getNickname()));
            $avatar = $googleUser->getAvatar();

            $emailVerified = filter_var(
                data_get($googleUser->user, 'email_verified', false),
                FILTER_VALIDATE_BOOLEAN
            );

            if ($googleId === '' || $email === '' || ! $emailVerified) {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'google' => 'Akun Google tidak menyediakan alamat email yang sudah terverifikasi.',
                    ]);
            }

            $user = DB::transaction(function () use (
                $googleId,
                $email,
                $name,
                $avatar
            ): User {
                $user = User::query()
                    ->where('google_id', $googleId)
                    ->first();

                if (! $user) {
                    $user = User::query()
                        ->where('email', $email)
                        ->first();
                }

                if ($user) {
                    $user->forceFill([
                        'google_id' => $googleId,
                        'google_avatar' => $avatar,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                        'name' => $user->name ?: ($name ?: Str::before($email, '@')),
                    ])->save();

                    return $user->refresh();
                }

                return User::create([
                    'name' => $name ?: Str::before($email, '@'),
                    'email' => $email,
                    'password' => Hash::make(Str::random(64)),
                    'google_id' => $googleId,
                    'google_avatar' => $avatar,
                    'email_verified_at' => now(),
                ]);
            });

            Auth::login($user, true);
            request()->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->withErrors([
                    'google' => 'Login dengan Google belum berhasil. Silakan coba kembali.',
                ]);
        }
    }

    private function googleIsConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }
}
