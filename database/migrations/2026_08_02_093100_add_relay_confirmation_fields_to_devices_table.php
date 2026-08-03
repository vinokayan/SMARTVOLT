<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            if (! Schema::hasColumn('devices', 'is_online')) {
                $table->boolean('is_online')
                    ->default(false)
                    ->after('status');
            }

            if (! Schema::hasColumn('devices', 'last_seen_at')) {
                $table->timestamp('last_seen_at')
                    ->nullable()
                    ->after('is_online');
            }

            if (! Schema::hasColumn('devices', 'last_confirmed_at')) {
                $table->timestamp('last_confirmed_at')
                    ->nullable()
                    ->after('last_seen_at');
            }

            if (! Schema::hasColumn('devices', 'last_command_at')) {
                $table->timestamp('last_command_at')
                    ->nullable()
                    ->after('last_confirmed_at');
            }

            if (! Schema::hasColumn('devices', 'last_command_id')) {
                $table->string('last_command_id', 100)
                    ->nullable()
                    ->after('last_command_at');
            }

            if (! Schema::hasColumn('devices', 'last_ack_command_id')) {
                $table->string('last_ack_command_id', 100)
                    ->nullable()
                    ->after('last_command_id');
            }

            if (! Schema::hasColumn('devices', 'pending_state')) {
                $table->boolean('pending_state')
                    ->nullable()
                    ->after('last_ack_command_id');
            }

            if (! Schema::hasColumn('devices', 'last_command_success')) {
                $table->boolean('last_command_success')
                    ->nullable()
                    ->after('pending_state');
            }
        });

        /*
         * Migration lama membuat esp32_device_id unik per baris. Padahal satu
         * ESP32 dapat memiliki beberapa relay. Pada MySQL, hapus unique lama
         * lalu gunakan kombinasi esp_unit_id + relay_code.
         */
        if (DB::getDriverName() === 'mysql') {
            $oldUniqueExists = collect(DB::select(
                "SHOW INDEX FROM devices WHERE Key_name = 'devices_esp32_device_id_unique'"
            ))->isNotEmpty();

            if ($oldUniqueExists) {
                Schema::table('devices', function (Blueprint $table) {
                    $table->dropUnique('devices_esp32_device_id_unique');
                });
            }

            $compositeExists = collect(DB::select(
                "SHOW INDEX FROM devices WHERE Key_name = 'devices_esp_unit_relay_unique'"
            ))->isNotEmpty();

            if (! $compositeExists) {
                Schema::table('devices', function (Blueprint $table) {
                    $table->unique(
                        ['esp_unit_id', 'relay_code'],
                        'devices_esp_unit_relay_unique'
                    );
                });
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $compositeExists = collect(DB::select(
                "SHOW INDEX FROM devices WHERE Key_name = 'devices_esp_unit_relay_unique'"
            ))->isNotEmpty();

            if ($compositeExists) {
                Schema::table('devices', function (Blueprint $table) {
                    $table->dropUnique('devices_esp_unit_relay_unique');
                });
            }
        }

        Schema::table('devices', function (Blueprint $table) {
            $columns = [
                'is_online',
                'last_seen_at',
                'last_confirmed_at',
                'last_command_at',
                'last_command_id',
                'last_ack_command_id',
                'pending_state',
                'last_command_success',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('devices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
