<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AdvancedModeController extends Controller
{
    /**
     * Membuka Mode Teknisi setelah PIN berhasil diverifikasi.
     */
    public function enable(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'pin' => ['required', 'string', 'max:32'],
            ],
            [
                'pin.required' => 'Masukkan PIN Teknisi.',
                'pin.string' => 'PIN Teknisi tidak valid.',
                'pin.max' => 'PIN Teknisi terlalu panjang.',
            ]
        );

        $submittedPin = trim((string) $validated['pin']);
        $correctPin = trim(
            (string) config('smartvolt.advanced_pin', '')
        );

        if (
            $correctPin === ''
            || ! hash_equals($correctPin, $submittedPin)
        ) {
            return redirect()
                ->route('technician.index')
                ->withErrors(
                    [
                        'advanced_mode' =>
                            'PIN yang dimasukkan tidak sesuai.',
                    ],
                    'technician'
                );
        }

        /*
         * Regenerasi ID session setelah hak akses ditingkatkan.
         * Akses tidak memiliki batas waktu selama pengguna masih berada
         * di Mode Teknisi.
         */
        $request->session()->regenerate();
        $request->session()->put([
            'advanced_mode' => true,
            'advanced_mode_verified_at' => now()->timestamp,
            'advanced_mode_visit_token' => Str::random(40),
        ]);

        $request->session()->forget('advanced_mode_left_at');

        return redirect()
            ->route('technician.index')
            ->with('status', 'Mode Teknisi berhasil dibuka.')
            ->with('open_advanced_panel', true);
    }

    /**
     * Mencatat waktu ketika pengguna meninggalkan Mode Teknisi.
     * Dipanggil oleh halaman melalui fetch atau sendBeacon.
     */
    public function leave(Request $request): Response
    {
        if (! (bool) $request->session()->get('advanced_mode', false)) {
            return response()->noContent();
        }

        $submittedToken = (string) $request->input('visit_token', '');
        $sessionToken = (string) $request->session()->get(
            'advanced_mode_visit_token',
            ''
        );

        /*
         * Token mencegah permintaan lama dari halaman sebelumnya
         * menandai sesi baru sebagai sedang berada di luar Mode Teknisi.
         */
        if (
            $submittedToken === ''
            || $sessionToken === ''
            || ! hash_equals($sessionToken, $submittedToken)
        ) {
            return response()->noContent();
        }

        if (! $request->session()->has('advanced_mode_left_at')) {
            $request->session()->put(
                'advanced_mode_left_at',
                now()->timestamp
            );
        }

        return response()->noContent();
    }

    /**
     * Menutup Mode Teknisi secara manual.
     * Setelah tombol ini digunakan, PIN langsung diperlukan kembali.
     */
    public function disable(Request $request): RedirectResponse
    {
        $this->forgetAdvancedMode($request);
        $request->session()->regenerate();

        return redirect()
            ->route('technician.index')
            ->with('status', 'Mode Teknisi telah ditutup.');
    }

    private function forgetAdvancedMode(Request $request): void
    {
        $request->session()->forget([
            'advanced_mode',
            'advanced_mode_verified_at',
            'advanced_mode_left_at',
            'advanced_mode_visit_token',
        ]);
    }
}
