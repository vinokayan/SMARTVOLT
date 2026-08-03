<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdvancedMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $isActive = (bool) $request->session()->get(
            'advanced_mode',
            false
        );

        $leftAt = (int) $request->session()->get(
            'advanced_mode_left_at',
            0
        );

        $graceSeconds = max(
            1,
            (int) config(
                'smartvolt.advanced_mode_reentry_grace_seconds',
                60
            )
        );

        $mustVerifyAgain = ! $isActive
            || (
                $leftAt > 0
                && now()->timestamp - $leftAt >= $graceSeconds
            );

        if ($mustVerifyAgain) {
            $this->forgetAdvancedMode($request);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Masukkan PIN Teknisi untuk melanjutkan.',
                ], 403);
            }

            return redirect()
                ->route('technician.index')
                ->withErrors(
                    [
                        'advanced_mode' =>
                            'Masukkan PIN untuk membuka Mode Teknisi.',
                    ],
                    'technician'
                );
        }

        if ($leftAt > 0) {
            $request->session()->forget('advanced_mode_left_at');
        }

        return $next($request);
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
