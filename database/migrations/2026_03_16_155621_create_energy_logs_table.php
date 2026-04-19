<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_logs', function (Blueprint $table) {
            $table->id();
            $table->float('voltage');
            $table->float('current');
            $table->float('power');
            $table->float('energy');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_logs');
    }
};