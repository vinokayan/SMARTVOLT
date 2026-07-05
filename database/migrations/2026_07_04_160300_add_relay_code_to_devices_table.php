<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom relay_code pada tabel devices.
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            if (! Schema::hasColumn('devices', 'relay_code')) {
                $table->string('relay_code', 20)
                    ->nullable()
                    ->after('esp32_device_id');
            }
        });
    }

    /**
     * Menghapus kolom relay_code jika rollback.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            if (Schema::hasColumn('devices', 'relay_code')) {
                $table->dropColumn('relay_code');
            }
        });
    }
};