<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            if (!Schema::hasColumn('devices', 'esp32_device_id')) {
                $table->string('esp32_device_id')->nullable()->unique()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            if (Schema::hasColumn('devices', 'esp32_device_id')) {
                $table->dropUnique(['esp32_device_id']);
                $table->dropColumn('esp32_device_id');
            }
        });
    }
};