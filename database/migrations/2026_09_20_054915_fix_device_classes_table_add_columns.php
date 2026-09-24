<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_classes', function (Blueprint $table) {

            if (!Schema::hasColumn('device_classes', 'name')) {
                $table->string('name', 100)
                    ->unique()
                    ->after('id');
            }

            if (!Schema::hasColumn('device_classes', 'category')) {
                $table->string('category', 100)
                    ->nullable()
                    ->after('name');
            }

            if (!Schema::hasColumn('device_classes', 'description')) {
                $table->text('description')
                    ->nullable()
                    ->after('category');
            }

        });
    }


    public function down(): void
    {
        Schema::table('device_classes', function (Blueprint $table) {

            $columns = [
                'name',
                'category',
                'description',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('device_classes', $column)) {
                    $table->dropColumn($column);
                }
            }

        });
    }
};