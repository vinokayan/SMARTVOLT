<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();

            // Nama tarif (biar mudah dibaca di admin panel)
            $table->string('name');

            // Golongan daya (VA)
            $table->integer('va_class'); 
            // contoh: 450, 900, 1300, 2200

            // Tarif per kWh (angka utama untuk perhitungan)
            $table->decimal('rate_per_kwh', 10, 2);
            // contoh: 415.00, 1444.70

            // Status aktif (biar bisa switch tarif tanpa hapus data)
            $table->boolean('is_active')->default(true);

            // Catatan tambahan (opsional)
            $table->text('description')->nullable();

            $table->timestamps();

            // Index biar query cepat saat cari berdasarkan VA
            $table->index('va_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};