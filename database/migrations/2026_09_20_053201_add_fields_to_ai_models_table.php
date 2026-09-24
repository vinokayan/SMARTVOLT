<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {

            $table->string('name', 100)
                ->after('id');

            $table->string('version', 50)
                ->after('name');

            $table->decimal('accuracy', 5, 2)
                ->nullable()
                ->after('version');

            $table->string('status', 50)
                ->default('development')
                ->after('accuracy');

            $table->string('model_path')
                ->nullable()
                ->after('status');

            $table->unique([
                'name',
                'version'
            ]);

        });
    }

    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {

            $table->dropColumn([
                'name',
                'version',
                'accuracy',
                'status',
                'model_path',
            ]);

        });
    }
};