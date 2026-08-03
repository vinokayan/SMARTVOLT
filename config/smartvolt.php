<?php

return [
    'advanced_pin' => trim(
        (string) env('SMARTVOLT_ADVANCED_PIN', '246810')
    ),

    /*
     * Waktu yang diberikan untuk kembali ke Mode Teknisi setelah keluar.
     * Selama pengguna tetap berada di Mode Teknisi, tidak ada batas waktu.
     */
    'advanced_mode_reentry_grace_seconds' => max(
        1,
        (int) env(
            'SMARTVOLT_ADVANCED_MODE_REENTRY_GRACE_SECONDS',
            60
        )
    ),
];
