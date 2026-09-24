<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

    public function up(): void
    {
        Schema::create('nilm_features', function (Blueprint $table) {

            $table->id();


            $table->foreignId('energy_log_id')
                ->constrained()
                ->cascadeOnDelete();


            $table->float('power_delta')
                ->default(0);


            $table->float('current_delta')
                ->default(0);


            $table->float('voltage')
                ->nullable();


            $table->float('power_factor')
                ->nullable();


            $table->float('duration_seconds')
                ->default(0);


            $table->timestamp('feature_time');


            $table->timestamps();


            $table->unique('energy_log_id');

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('nilm_features');
    }
};