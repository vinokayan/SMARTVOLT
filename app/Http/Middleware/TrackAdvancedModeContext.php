<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackAdvancedModeContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) $request->session()->get('advanced_mode', false)) {
            $request->session()->forget([
                'advanced_mode_left_at',
                'advanced_mode_visit_token',
            ]);

            return $next($request);
        }

        $routeName = (string) ($request->route()?->getName() ?? '');

        if ($routeName === 'advanced-mode.leave') {
            return $next($request);
        }

        $leftAt = (int) $request->session()->get(
            'advanced_mode_left_at',
            0
        );

        if ($this->isTechnicianContext($routeName)) {
            if ($leftAt > 0 && $this->hasGracePeriodEnded($leftAt)) {
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
                                'Akses sebelumnya telah berakhir. Masukkan PIN kembali.',
                        ],
                        'technician'
                    );
            }

            if ($leftAt > 0) {
                $request->session()->forget('advanced_mode_left_at');
            }

            if ($routeName === 'technician.index') {
                $request->session()->put(
                    'advanced_mode_visit_token',
                    Str::random(40)
                );
            }

            return $next($request);
        }

        if ($leftAt <= 0 && $this->isPageNavigation($request)) {
            $request->session()->put(
                'advanced_mode_left_at',
                now()->timestamp
            );
        } elseif ($leftAt > 0 && $this->hasGracePeriodEnded($leftAt)) {
            $this->forgetAdvancedMode($request);
        }

        return $next($request);
    }

    private function isTechnicianContext(string $routeName): bool
    {
        if (str_starts_with($routeName, 'technician.')) {
            return true;
        }

        return in_array($routeName, [
            'rooms.store',
            'rooms.update',
            'rooms.destroy',
            'devices.store',
            'devices.update',
            'devices.destroy',
            'advanced-mode.enable',
            'advanced-mode.disable',
        ], true);
    }

    private function isPageNavigation(Request $request): bool
    {
        return $request->isMethod('GET')
            && ! $request->expectsJson()
            && ! $request->ajax();
    }

    private function hasGracePeriodEnded(int $leftAt): bool
    {
        $graceSeconds = max(
            1,
            (int) config(
                'smartvolt.advanced_mode_reentry_grace_seconds',
                60
            )
        );

        return now()->timestamp - $leftAt >= $graceSeconds;
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
