<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

    public function up(): void
    {
        Schema::table('nilm_features', function (Blueprint $table) {

            $table->float('current')
                ->nullable()
                ->after('current_delta');


            $table->float('power')
                ->nullable()
                ->after('current');


            $table->float('frequency')
                ->nullable()
                ->after('power');

        });
    }


    public function down(): void
    {
        Schema::table('nilm_features', function (Blueprint $table) {

            $table->dropColumn([
                'current',
                'power',
                'frequency'
            ]);

        });
    }
};