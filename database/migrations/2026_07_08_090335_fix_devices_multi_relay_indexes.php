<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    /**
     * Menjalankan perubahan struktur database.
     */
    public function up(): void
    {
        /*
         * Tambahkan relay_code apabila belum tersedia.
         *
         * Tidak menggunakan after() agar migration tetap kompatibel
         * dengan MySQL, MariaDB, dan SQLite.
         */
        if (! Schema::hasColumn('devices', 'relay_code')) {
            Schema::table('devices', function (Blueprint $table): void {
                $table->string('relay_code', 50)
                    ->nullable();
            });
        }

        /*
         * Tambahkan esp_unit_id apabila belum tersedia.
         */
        if (! Schema::hasColumn('devices', 'esp_unit_id')) {
            Schema::table('devices', function (Blueprint $table): void {
                $table->string('esp_unit_id', 100)
                    ->nullable();
            });
        }

        /*
         * Versi lama menjadikan esp32_device_id unik.
         * Aturan tersebut membuat satu ESP32 tidak dapat memiliki
         * beberapa relay.
         */
        foreach ($this->uniqueIndexesOnlyForEsp32DeviceId() as $indexName) {
            Schema::table(
                'devices',
                function (Blueprint $table) use ($indexName): void {
                    $table->dropUnique($indexName);
                }
            );
        }

        /*
         * esp32_device_id tetap diberi index biasa agar pencarian
         * perangkat berdasarkan ESP32 tetap efisien.
         */
        if (! $this->indexExists(
            'devices',
            'devices_esp32_device_id_index'
        )) {
            Schema::table('devices', function (Blueprint $table): void {
                $table->index(
                    'esp32_device_id',
                    'devices_esp32_device_id_index'
                );
            });
        }

        /*
         * Satu kombinasi ESP Unit ID dan relay_code hanya boleh
         * dimiliki oleh satu perangkat.
         */
        if (! $this->indexExists(
            'devices',
            'devices_esp_unit_relay_unique'
        )) {
            $duplicates = DB::table('devices')
                ->select(
                    'esp_unit_id',
                    'relay_code',
                    DB::raw('COUNT(*) AS total')
                )
                ->whereNotNull('esp_unit_id')
                ->whereNotNull('relay_code')
                ->groupBy('esp_unit_id', 'relay_code')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            if ($duplicates->isNotEmpty()) {
                throw new \RuntimeException(
                    'Migration dihentikan karena terdapat kombinasi '
                    . 'esp_unit_id dan relay_code yang duplikat.'
                );
            }

            Schema::table('devices', function (Blueprint $table): void {
                $table->unique(
                    ['esp_unit_id', 'relay_code'],
                    'devices_esp_unit_relay_unique'
                );
            });
        }
    }

    /**
     * Membatalkan perubahan migration.
     */
    public function down(): void
    {
        /*
         * Unique index lama pada esp32_device_id tidak dikembalikan,
         * karena dapat merusak konfigurasi multi-relay.
         */
        if ($this->indexExists(
            'devices',
            'devices_esp_unit_relay_unique'
        )) {
            Schema::table('devices', function (Blueprint $table): void {
                $table->dropUnique(
                    'devices_esp_unit_relay_unique'
                );
            });
        }
    }

    /**
     * Memeriksa keberadaan index secara lintas database.
     */
    private function indexExists(
        string $table,
        string $indexName
    ): bool {
        return collect(Schema::getIndexes($table))
            ->contains(function (array $index) use ($indexName): bool {
                return ($index['name'] ?? null) === $indexName;
            });
    }

    /**
     * Mengambil semua unique index yang hanya menggunakan
     * kolom esp32_device_id.
     *
     * @return array<int, string>
     */
    private function uniqueIndexesOnlyForEsp32DeviceId(): array
    {
        return collect(Schema::getIndexes('devices'))
            ->filter(function (array $index): bool {
                $isUnique = (bool) ($index['unique'] ?? false);

                $columns = array_values(
                    $index['columns'] ?? []
                );

                return $isUnique
                    && $columns === ['esp32_device_id'];
            })
            ->pluck('name')
            ->filter(fn (mixed $name): bool => is_string($name))
            ->values()
            ->all();
    }
};
