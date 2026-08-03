<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Mengarahkan pengguna ke halaman autentikasi Google.
     */
    public function redirect(Request $request)
    {
        $intent = $request->query('from') === 'register'
            ? 'register'
            : 'login';

        $request->session()->put('google_auth_intent', $intent);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Menerima callback dari Google.
     */
    public function callback(Request $request)
    {
        $intent = $request->session()->get(
            'google_auth_intent',
            'login'
        );

        $returnRoute = $intent === 'register'
            ? 'register'
            : 'login';

        try {
            /*
             * Mengambil data pengguna dari Google.
             */
            $googleUser = Socialite::driver('google')->user();

            $email = strtolower(
                trim((string) $googleUser->getEmail())
            );

            if (
                empty($email) ||
                !filter_var($email, FILTER_VALIDATE_EMAIL)
            ) {
                throw ValidationException::withMessages([
                    'email' => 'Google tidak memberikan alamat email yang valid.',
                ]);
            }

            /*
             * Cari pengguna berdasarkan email.
             */
            $user = User::query()
                ->where('email', $email)
                ->first();

            /*
             * Jika email belum terdaftar, buat akun SmartVolt baru.
             */
            if (!$user) {
                $user = new User();

                $user->name = $googleUser->getName()
                    ?: $googleUser->getNickname()
                    ?: 'Pengguna SmartVolt';

                $user->email = $email;

                /*
                 * Password acak dibuat karena kolom password
                 * pada database SmartVolt tidak boleh kosong.
                 */
                $user->password = Hash::make(
                    Str::random(64)
                );

                $user->email_verified_at = now();

                $user->save();
            } else {
                /*
                 * Tandai email terverifikasi jika sebelumnya belum.
                 */
                if (empty($user->email_verified_at)) {
                    $user->email_verified_at = now();
                    $user->save();
                }
            }

            /*
             * Masukkan pengguna ke sistem SmartVolt.
             */
            Auth::login($user, true);

            /*
             * Regenerasi session untuk keamanan.
             */
            $request->session()->forget(
                'google_auth_intent'
            );

            $request->session()->regenerate();

            return redirect()
                ->route('dashboard')
                ->with(
                    'status',
                    $intent === 'register'
                        ? 'Akun SmartVolt berhasil dibuat menggunakan Google.'
                        : 'Berhasil masuk menggunakan akun Google.'
                );
        } catch (ValidationException $exception) {
            Log::warning('Validasi Google OAuth gagal.', [
                'errors' => $exception->errors(),
            ]);

            return redirect()
                ->route($returnRoute)
                ->withErrors($exception->errors());
        } catch (InvalidStateException $exception) {
            Log::warning('State Google OAuth tidak valid.', [
                'message' => $exception->getMessage(),
                'session_id' => $request->session()->getId(),
                'url' => $request->fullUrl(),
            ]);

            $request->session()->forget(
                'google_auth_intent'
            );

            return redirect()
                ->route($returnRoute)
                ->with(
                    'error',
                    'Sesi Google tidak valid. Tutup halaman Google, lalu buka SmartVolt melalui http://127.0.0.1:8000 dan coba kembali.'
                );
        } catch (Throwable $exception) {
            Log::error('Login Google SmartVolt gagal.', [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            $request->session()->forget(
                'google_auth_intent'
            );

            /*
             * Saat mode lokal, tampilkan penyebab asli.
             */
            $message = config('app.debug')
                ? 'Login Google gagal: ' . $exception->getMessage()
                : 'Login dengan Google belum berhasil. Silakan coba kembali.';

            return redirect()
                ->route($returnRoute)
                ->with('error', $message);
        }
    }
}