<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {

            $table->foreignId('ai_model_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('dataset_name')
                ->after('ai_model_id');

            $table->integer('epoch')
                ->nullable()
                ->after('dataset_name');

            $table->decimal('loss',10,6)
                ->nullable()
                ->after('epoch');

            $table->decimal('accuracy',5,2)
                ->nullable()
                ->after('loss');

            $table->timestamp('trained_at')
                ->nullable()
                ->after('accuracy');

        });
    }


    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {

            $table->dropForeign(['ai_model_id']);

            $table->dropColumn([
                'ai_model_id',
                'dataset_name',
                'epoch',
                'loss',
                'accuracy',
                'trained_at',
            ]);

        });
    }
};