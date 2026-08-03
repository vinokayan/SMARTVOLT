<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_DECAY_SECONDS = 60;

    public function showLoginForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
                'remember' => ['nullable', 'boolean'],
            ],
            [
                'email.required' => 'Alamat email harus diisi.',
                'email.email' => 'Format alamat email belum benar.',
                'password.required' => 'Kata sandi harus diisi.',
            ]
        );

        $email = Str::lower(trim((string) $validated['email']));
        $throttleKey = $this->loginThrottleKey($email, $request);

        if (RateLimiter::tooManyAttempts(
            $throttleKey,
            self::MAX_LOGIN_ATTEMPTS
        )) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan masuk. Coba kembali dalam {$seconds} detik.",
            ]);
        }

        $credentials = [
            'email' => $email,
            'password' => (string) $validated['password'],
        ];

        if (! Auth::attempt(
            $credentials,
            $request->boolean('remember')
        )) {
            RateLimiter::hit(
                $throttleKey,
                self::LOGIN_DECAY_SECONDS
            );

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi yang dimasukkan tidak sesuai.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:100'],
                'email' => [
                    'required',
                    'email',
                    'max:100',
                    'unique:users,email',
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ],
            [
                'name.required' => 'Nama lengkap harus diisi.',
                'name.max' => 'Nama lengkap maksimal 100 karakter.',
                'email.required' => 'Alamat email harus diisi.',
                'email.email' => 'Format alamat email belum benar.',
                'email.unique' => 'Alamat email tersebut sudah digunakan.',
                'password.required' => 'Kata sandi harus diisi.',
                'password.min' => 'Kata sandi minimal 8 karakter.',
                'password.confirmed' => 'Konfirmasi kata sandi belum sama.',
            ]
        );

        $user = User::create([
            'name' => trim((string) $validated['name']),
            'email' => Str::lower(trim((string) $validated['email'])),
            'password' => Hash::make(
                (string) $validated['password']
            ),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Anda berhasil keluar dari SmartVolt.');
    }

    public function showForgotPasswordForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    public function sendResetLink(
        Request $request
    ): RedirectResponse {
        $request->validate(
            [
                'email' => ['required', 'email'],
            ],
            [
                'email.required' => 'Alamat email harus diisi.',
                'email.email' => 'Format alamat email belum benar.',
            ]
        );

        $status = Password::sendResetLink([
            'email' => Str::lower(
                trim((string) $request->input('email'))
            ),
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return redirect()
                ->route('login')
                ->with(
                    'status',
                    'Tautan pengaturan ulang kata sandi berhasil dikirim.'
                );
        }

        return back()
            ->withErrors([
                'email' => __($status),
            ])
            ->withInput();
    }

    public function showResetPasswordForm(
        Request $request,
        string $token
    ): View {
        return view('auth.reset_password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function resetPassword(
        Request $request
    ): RedirectResponse {
        $request->validate(
            [
                'token' => ['required'],
                'email' => ['required', 'email'],
                'password' => [
                    'required',
                    'confirmed',
                    'min:8',
                ],
            ],
            [
                'email.required' => 'Alamat email harus diisi.',
                'email.email' => 'Format alamat email belum benar.',
                'password.required' => 'Kata sandi baru harus diisi.',
                'password.min' => 'Kata sandi minimal 8 karakter.',
                'password.confirmed' => 'Konfirmasi kata sandi belum sama.',
            ]
        );

        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with(
                    'status',
                    'Kata sandi berhasil diubah. Silakan masuk kembali.'
                );
        }

        return back()
            ->withErrors([
                'email' => __($status),
            ])
            ->withInput();
    }

    private function loginThrottleKey(
        string $email,
        Request $request
    ): string {
        return Str::transliterate(
            Str::lower($email) . '|' . $request->ip()
        );
    }
}
