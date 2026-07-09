<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_commands', function (Blueprint $table) {
            $table->id();

            $table->foreignId('device_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('command_id', 36)->unique();

            $table->string('esp_unit_id', 100);
            $table->string('relay_code', 50);

            $table->boolean('requested_state');
            $table->boolean('applied_state')->nullable();

            $table->string('status', 20)->default('pending');
            // pending, applied, failed, timeout

            $table->string('source', 50)->default('dashboard');
            $table->text('error_message')->nullable();

            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index([
                'esp_unit_id',
                'relay_code',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_commands');
    }
};