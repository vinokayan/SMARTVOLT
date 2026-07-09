<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('energy_logs', function (Blueprint $table) {
            $table->timestamp('observed_at')
                ->nullable()
                ->after('device_id');

            $table->string('telemetry_id', 100)
                ->nullable()
                ->after('observed_at');
        });

        /*
         * Data lama belum memiliki observed_at.
         * Gunakan created_at sebagai waktu pembacaan lama.
         */
        DB::table('energy_logs')
            ->whereNull('observed_at')
            ->update([
                'observed_at' => DB::raw('created_at'),
            ]);

        Schema::table('energy_logs', function (Blueprint $table) {
            $table->index(
                ['device_id', 'observed_at'],
                'energy_logs_device_observed_at_index'
            );

            /*
             * Mencegah telemetry yang sama tersimpan dua kali
             * saat ESP32 retry request.
             */
            $table->unique(
                ['device_id', 'telemetry_id'],
                'energy_logs_device_telemetry_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('energy_logs', function (Blueprint $table) {
            $table->dropUnique('energy_logs_device_telemetry_unique');
            $table->dropIndex('energy_logs_device_observed_at_index');

            $table->dropColumn([
                'telemetry_id',
                'observed_at',
            ]);
        });
    }
};