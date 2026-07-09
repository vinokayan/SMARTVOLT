<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_meters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('room_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('esp_unit_id', 100);
            $table->string('meter_code', 50)->default('main');

            $table->string('name', 100);
            $table->string('sensor_type', 50)->default('PZEM004T');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['esp_unit_id', 'meter_code'],
                'energy_meters_esp_unit_meter_code_unique'
            );

            $table->index(
                ['user_id', 'room_id'],
                'energy_meters_user_room_index'
            );
        });

        Schema::table('energy_logs', function (Blueprint $table) {
            /*
             * Telemetry baru akan tersambung ke meter PZEM,
             * bukan ke relay/device.
             */
            $table->foreignId('energy_meter_id')
                ->nullable()
                ->after('device_id')
                ->constrained('energy_meters')
                ->restrictOnDelete();

            $table->index(
                ['energy_meter_id', 'observed_at'],
                'energy_logs_meter_observed_at_index'
            );

            /*
             * Mencegah data ganda jika ESP32 retry request.
             */
            $table->unique(
                ['energy_meter_id', 'telemetry_id'],
                'energy_logs_meter_telemetry_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('energy_logs', function (Blueprint $table) {
            $table->dropUnique('energy_logs_meter_telemetry_unique');
            $table->dropIndex('energy_logs_meter_observed_at_index');
            $table->dropForeign(['energy_meter_id']);
            $table->dropColumn('energy_meter_id');
        });

        Schema::dropIfExists('energy_meters');
    }
};