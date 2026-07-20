<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_daily_summaries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('energy_meter_id')
                ->constrained('energy_meters')
                ->cascadeOnDelete();

            $table->date('summary_date');

            $table->decimal('energy_start', 12, 4)->default(0);
            $table->decimal('energy_end', 12, 4)->default(0);
            $table->decimal('usage_kwh', 12, 4)->default(0);

            $table->decimal('avg_voltage', 8, 2)->default(0);
            $table->decimal('max_power', 10, 2)->default(0);
            $table->decimal('last_voltage', 8, 2)->default(0);
            $table->decimal('last_current', 8, 3)->default(0);
            $table->decimal('last_power', 10, 2)->default(0);

            $table->decimal('tariff_per_kwh', 12, 2)->default(1444);
            $table->decimal('estimated_cost', 14, 2)->default(0);

            $table->unsignedInteger('sample_count')->default(0);
            $table->timestamp('last_observed_at')->nullable();

            $table->timestamps();

            $table->unique(['energy_meter_id', 'summary_date'], 'meter_daily_unique');
            $table->index(['user_id', 'summary_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_daily_summaries');
    }
};