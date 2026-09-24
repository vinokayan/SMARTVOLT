<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nilm_predictions', function (Blueprint $table) {

            $table->foreignId('energy_log_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();


            $table->foreignId('device_class_id')
                ->after('energy_log_id')
                ->constrained()
                ->cascadeOnDelete();


            $table->foreignId('ai_model_id')
                ->nullable()
                ->after('device_class_id')
                ->constrained()
                ->nullOnDelete();


            $table->decimal('estimated_power',10,2)
                ->after('ai_model_id');


            $table->decimal('confidence',5,2)
                ->nullable()
                ->after('estimated_power');


            $table->timestamp('predicted_at')
                ->after('confidence');


        });
    }


    public function down(): void
    {
        Schema::table('nilm_predictions', function (Blueprint $table) {

            $table->dropForeign(['energy_log_id']);
            $table->dropForeign(['device_class_id']);
            $table->dropForeign(['ai_model_id']);

            $table->dropColumn([
                'energy_log_id',
                'device_class_id',
                'ai_model_id',
                'estimated_power',
                'confidence',
                'predicted_at',
            ]);

        });
    }
};